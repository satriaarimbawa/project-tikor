<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;

class TelegramNotifierService
{
    protected $database;

    public function __construct(Database $database = null)
    {
        $this->database = $database;
    }

    /**
     * Get telegram configuration from Firebase or .env fallback.
     */
    public function getConfig(): array
    {
        $config = [
            'is_active' => true,
            'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
            'chat_id'   => env('TELEGRAM_CHAT_ID', '')
        ];

        try {
            if ($this->database) {
                $saved = $this->database->getReference('settings/telegram')->getValue();
                if (is_array($saved)) {
                    $config['is_active'] = $saved['is_active'] ?? true;
                    if (!empty($saved['bot_token'])) $config['bot_token'] = $saved['bot_token'];
                    if (!empty($saved['chat_id'])) $config['chat_id'] = $saved['chat_id'];
                }
            }
        } catch (\Throwable $e) {
            // Fallback to env
        }

        return $config;
    }

    /**
     * Send direct message to Telegram Bot API.
     */
    public function sendMessage(string $message, string $botToken = null, string $chatId = null): array
    {
        $config = $this->getConfig();
        $token = $botToken ?: $config['bot_token'];
        $chat = $chatId ?: $config['chat_id'];

        if (empty($token) || empty($chat)) {
            return ['status' => false, 'message' => 'Bot Token atau Chat ID belum dikonfigurasi.'];
        }

        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $response = Http::timeout(5)->post($url, [
                'chat_id'    => $chat,
                'text'       => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                return ['status' => true, 'message' => 'Pesan berhasil dikirim ke Telegram.'];
            }

            Log::warning("Telegram API Error: " . $response->body());
            return ['status' => false, 'message' => 'Telegram API Error: ' . ($response->json('description') ?? $response->body())];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim notifikasi Telegram: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send photo with caption to Telegram Bot API.
     */
    public function sendPhoto(string $photoPath, string $caption = '', string $botToken = null, string $chatId = null): array
    {
        $config = $this->getConfig();
        $token = $botToken ?: $config['bot_token'];
        $chat = $chatId ?: $config['chat_id'];

        if (empty($token) || empty($chat)) {
            return ['status' => false, 'message' => 'Bot Token atau Chat ID belum dikonfigurasi.'];
        }

        if (!file_exists($photoPath)) {
            return ['status' => false, 'message' => "File foto tidak ditemukan: {$photoPath}"];
        }

        try {
            $url = "https://api.telegram.org/bot{$token}/sendPhoto";
            $response = Http::timeout(30)
                ->attach('photo', file_get_contents($photoPath), basename($photoPath))
                ->post($url, [
                    'chat_id'    => $chat,
                    'caption'    => $caption,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful()) {
                return ['status' => true, 'message' => 'Foto berhasil dikirim ke Telegram.'];
            }

            Log::warning("Telegram sendPhoto API Error: " . $response->body());
            return ['status' => false, 'message' => 'Telegram API Error: ' . ($response->json('description') ?? $response->body())];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim foto Telegram: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Realtime report of unhandled exceptions.
     */
    public function report(\Throwable $e): void
    {
        $config = $this->getConfig();
        if (!$config['is_active'] || empty($config['bot_token']) || empty($config['chat_id'])) {
            return;
        }

        // Filter out non-critical exceptions (404, validation, auth)
        if ($this->shouldIgnore($e)) {
            return;
        }

        // Anti-Spam / Cooldown: 5 minutes per error signature
        $errorHash = 'tg_err_' . md5($e->getMessage() . $e->getFile() . $e->getLine());
        if (Cache::has($errorHash)) {
            return;
        }
        Cache::put($errorHash, true, now()->addMinutes(5));

        $time = Carbon::now('Asia/Makassar')->format('d M Y, H:i:s') . ' WITA';
        $user = session('username') ? session('username') . ' (' . ucfirst(session('role', 'user')) . ')' : 'Guest / System';
        $url = request() ? request()->method() . ' ' . request()->fullUrl() : 'CLI / Background';
        
        $errorClass = (new \ReflectionClass($e))->getShortName();
        $file = basename($e->getFile()) . ':' . $e->getLine();
        $cleanMessage = htmlspecialchars(mb_substr($e->getMessage(), 0, 500));

        // Generate quick troubleshooting hint
        $hint = $this->generateTroubleshootingHint($e);

        $html = "🚨 <b>[SERVER ERROR ALERT - SEDETIK DISHUB]</b>\n";
        $html .= "⏰ <b>Waktu:</b> {$time}\n";
        $html .= "🌐 <b>Request:</b> <code>{$url}</code>\n";
        $html .= "👤 <b>User:</b> {$user}\n";
        $html .= "📍 <b>Lokasi:</b> <code>{$file}</code>\n";
        $html .= "🏷️ <b>Jenis:</b> <code>{$errorClass}</code>\n\n";
        $html .= "💥 <b>Pesan Error:</b>\n<pre>{$cleanMessage}</pre>\n\n";
        if ($hint) {
            $html .= "💡 <b>Petunjuk Cepat:</b>\n{$hint}\n";
        }

        $this->sendMessage($html);
    }

    /**
     * Proactive Health Check Issue Reporting.
     */
    public function reportHealthIssue(array $issues): void
    {
        $config = $this->getConfig();
        if (!$config['is_active'] || empty($config['bot_token']) || empty($config['chat_id'])) {
            return;
        }

        $time = Carbon::now('Asia/Makassar')->format('d M Y, H:i:s') . ' WITA';

        $html = "⚠️ <b>[DETEKSI DINI: PERINGATAN KESEHATAN SERVER]</b>\n";
        $html .= "⏰ <b>Waktu Deteksi:</b> {$time}\n";
        $html .= "📍 <b>Target:</b> Server VM Sedetik Dishub\n\n";
        $html .= "Terdeteksi kendala pada sistem <b>sebelum pengguna mengakses aplikasi</b>:\n\n";

        foreach ($issues as $index => $issue) {
            $num = $index + 1;
            $html .= "<b>{$num}. {$issue['title']}</b>\n";
            $html .= "   • Gejala: <code>" . htmlspecialchars($issue['detail']) . "</code>\n";
            $html .= "   • Solusi: {$issue['recommendation']}\n\n";
        }

        $html .= "Segera lakukan pemeriksaan melalui Termius agar operasional operator tidak terganggu.";

        $this->sendMessage($html);
    }

    /**
     * Test notification triggered from Setting UI.
     */
    public function testNotification(string $botToken, string $chatId): array
    {
        $time = Carbon::now('Asia/Makassar')->format('d M Y, H:i:s') . ' WITA';
        $msg = "✅ <b>[TES NOTIFIKASI BERHASIL]</b>\n\n";
        $msg .= "Halo IT Support Dishub! Bot Telegram berhasil terhubung ke server <b>Sedetik Tikor</b>.\n";
        $msg .= "⏰ Waktu Tes: <code>{$time}</code>\n";
        $msg .= "🛡️ Status: <b>Siap menerima laporan error & deteksi dini server</b>.";

        return $this->sendMessage($msg, $botToken, $chatId);
    }

    private function shouldIgnore(\Throwable $e): bool
    {
        $ignoredClasses = [
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Session\TokenMismatchException::class,
        ];

        foreach ($ignoredClasses as $ignored) {
            if ($e instanceof $ignored) return true;
        }

        return false;
    }

    private function generateTroubleshootingHint(\Throwable $e): ?string
    {
        $msg = strtolower($e->getMessage());

        if (str_contains($msg, 'invalid_grant')) {
            return "Kredensial Firebase ditolak. Sinkronkan jam server dengan <code>sudo systemctl restart chrony</code> di VM Kominfo.";
        }
        if (str_contains($msg, 'timeout') || str_contains($msg, 'timed out') || str_contains($msg, 'could not resolve host')) {
            return "Koneksi internet server ke Firebase RTDB lambat/terputus. Periksa gateway internet VM.";
        }
        if (str_contains($msg, 'permission denied') || str_contains($msg, 'unauthorized')) {
            return "Periksa Security Rules di Firebase Console atau hak akses service account.";
        }
        if (str_contains($msg, 'no space left on device')) {
            return "Kapasitas disk penyimpanan server penuh! Segera bersihkan log / storage.";
        }

        return null;
    }
}
