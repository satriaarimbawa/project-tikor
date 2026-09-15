<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunPlaywrightTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:playwright {--url=http://127.0.0.1:8000 : Target base URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan pengujian E2E otomatis Playwright dan kirim screenshot ke Bot Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Menyiapkan Pengujian Playwright E2E & Telegram Reporter...');
        
        $scriptPath = base_path('tests/playwright/e2e_settings_telegram.mjs');
        if (!file_exists($scriptPath)) {
            $this->error("Script tidak ditemukan: {$scriptPath}");
            return Command::FAILURE;
        }

        $env = [
            'TELEGRAM_BOT_TOKEN' => env('TELEGRAM_BOT_TOKEN', '8696261109:AAGZJc4SZZn6NUkpFLJLpGQlegDmHvGBa5o'),
            'TELEGRAM_CHAT_ID' => env('TELEGRAM_CHAT_ID', '1163086634'),
            'BASE_URL' => $this->option('url'),
        ];

        $process = new Process(['node', $scriptPath], base_path(), $env, null, 300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if ($process->isSuccessful()) {
            $this->info('✅ Seluruh pengujian Playwright selesai dan screenshot telah dikirim ke Telegram!');
            return Command::SUCCESS;
        }

        $this->error('❌ Terjadi kesalahan saat pengujian Playwright.');
        return Command::FAILURE;
    }
}
