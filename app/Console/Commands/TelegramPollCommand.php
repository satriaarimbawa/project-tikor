<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramCommandHandler;
use App\Services\TelegramNotifierService;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll {--once : Jalankan hanya 1 siklus pemeriksaan update lalu keluar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan daemon polling untuk mendengarkan perintah interaktif (/health, /check, /test_error, /status) dari Bot Telegram';

    protected $commandHandler;
    protected $telegramNotifier;

    public function __construct(TelegramCommandHandler $commandHandler, TelegramNotifierService $telegramNotifier)
    {
        parent::__construct();
        $this->commandHandler = $commandHandler;
        $this->telegramNotifier = $telegramNotifier;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = $this->telegramNotifier->getConfig();
        $botToken = $config['bot_token'];

        if (empty($botToken)) {
            $this->error('❌ Bot Token Telegram belum dikonfigurasi di .env atau Pengaturan Sistem.');
            return Command::FAILURE;
        }

        $this->info("🤖 Memulai Telegram Bot Long-Polling Listener (@sedetik_bot)...");
        $this->info("💡 Anda dapat mengetik /health, /check, /test_error, /status di aplikasi Telegram.");
        $this->info("Tekan CTRL+C untuk menghentikan.\n");

        $offset = 0;
        $isOnce = $this->option('once');

        do {
            try {
                $url = "https://api.telegram.org/bot{$botToken}/getUpdates";
                $response = Http::timeout(30)->get($url, [
                    'offset'  => $offset,
                    'timeout' => $isOnce ? 2 : 10,
                ]);

                if ($response->successful()) {
                    $updates = $response->json('result', []);
                    foreach ($updates as $update) {
                        $updateId = $update['update_id'];
                        $offset = $updateId + 1;

                        $text = $update['message']['text'] ?? '';
                        $user = $update['message']['from']['first_name'] ?? 'User';

                        if (!empty($text)) {
                            $this->line("📩 [{$user}] Perintah diterima: <comment>{$text}</comment>");
                            $result = $this->commandHandler->handle($update);
                            if ($result) {
                                $this->info("   ↳ Respons berhasil dikirim untuk: {$result['command']}");
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("⚠️ Polling issue: " . $e->getMessage());
                sleep(2);
            }

            if ($isOnce) {
                break;
            }

            usleep(500000); // 0.5s pause
        } while (true);

        $this->info("✅ Polling siklus selesai.");
        return Command::SUCCESS;
    }
}
