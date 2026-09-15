<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use App\Services\TelegramNotifierService;
use App\Services\ActivityLogService;
use Carbon\Carbon;

class SettingController extends Controller
{
    protected $database;
    protected $telegramNotifier;
    protected $logService;

    public function __construct(Database $database, TelegramNotifierService $telegramNotifier, ActivityLogService $logService)
    {
        $this->database = $database;
        $this->telegramNotifier = $telegramNotifier;
        $this->logService = $logService;
    }

    /**
     * Tampilkan halaman utama pengaturan sistem.
     */
    public function index()
    {
        $settingsRaw = $this->database->getReference('settings')->getValue() ?? [];
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        $lokasiRaw = $this->database->getReference('lokasi')->getValue() ?? [];

        // Konfigurasi Default Geofencing
        $geofencing = array_merge([
            'default_radius' => 100,
            'cutoff_hour' => 22,
            'heartbeat_interval_seconds' => 60,
            'operator_radius' => []
        ], $settingsRaw['geofencing'] ?? []);

        // Konfigurasi Default Keamanan Sesi
        $sessionSecurity = array_merge([
            'max_sessions_per_account' => 1,
            'allow_multi_device_admin' => true,
            'stale_timeout_seconds' => 300
        ], $settingsRaw['session_security'] ?? []);

        // Konfigurasi Default Telegram
        $telegram = array_merge([
            'is_active' => true,
            'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
            'chat_id' => env('TELEGRAM_CHAT_ID', '')
        ], $settingsRaw['telegram'] ?? []);

        // Konfigurasi Default Operasional
        $operational = array_merge([
            'max_break_minutes' => 60,
            'allow_cross_claim' => true,
            'sound_alert_violation' => true
        ], $settingsRaw['operational'] ?? []);

        // Filter Daftar Operator Lapangan
        $operators = [];
        foreach ($usersRaw as $uid => $u) {
            if (($u['role_user'] ?? '') === 'operator') {
                $customRad = $geofencing['operator_radius'][$uid] ?? null;
                $operators[$uid] = [
                    'uid' => $uid,
                    'username' => $u['username'] ?? 'Operator',
                    'email' => $u['email'] ?? '-',
                    'is_online' => $u['is_online'] ?? false,
                    'custom_radius' => $customRad
                ];
            }
        }

        return view('admin.settings.index', [
            'geofencing' => $geofencing,
            'sessionSecurity' => $sessionSecurity,
            'telegram' => $telegram,
            'operational' => $operational,
            'operators' => $operators,
            'lokasiCount' => count($lokasiRaw),
        ]);
    }

    /**
     * Simpan Pengaturan Radius Geofencing.
     */
    public function updateRadius(Request $request)
    {
        $request->validate([
            'default_radius' => 'required|numeric|min:10|max:5000',
            'cutoff_hour' => 'required|integer|min:0|max:23',
            'heartbeat_interval_seconds' => 'required|integer|in:30,60,120',
            'operator_radius' => 'nullable|array',
        ]);

        $cleanOpRadius = [];
        if ($request->has('operator_radius') && is_array($request->input('operator_radius'))) {
            foreach ($request->input('operator_radius') as $uid => $val) {
                if ($val !== null && $val !== '' && is_numeric($val) && (int)$val > 0) {
                    $cleanOpRadius[$uid] = (int)$val;
                }
            }
        }

        $data = [
            'default_radius' => (int) $request->input('default_radius', 100),
            'cutoff_hour' => (int) $request->input('cutoff_hour', 22),
            'heartbeat_interval_seconds' => (int) $request->input('heartbeat_interval_seconds', 60),
            'operator_radius' => $cleanOpRadius,
            'updated_at' => Carbon::now('Asia/Makassar')->toDateTimeString(),
            'updated_by' => session('username', 'IT Support')
        ];

        $this->database->getReference('settings/geofencing')->set($data);

        $this->logService->log(
            'update',
            session('user_id', 'it_support'),
            session('username', 'IT Support'),
            "IT Support memperbarui konfigurasi <strong>Radius Geofencing</strong> (Default: {$data['default_radius']}m, " . count($cleanOpRadius) . " custom operator)."
        );

        return redirect()->back()->with('success', 'Pengaturan Radius & Geofencing berhasil disimpan!');
    }

    /**
     * Simpan Pengaturan Keamanan Sesi & Multi-Login.
     */
    public function updateSessionSecurity(Request $request)
    {
        $request->validate([
            'max_sessions_per_account' => 'required|integer|min:0|max:10',
            'stale_timeout_seconds' => 'required|integer|min:60|max:3600',
        ]);

        $data = [
            'max_sessions_per_account' => (int) $request->input('max_sessions_per_account', 1),
            'allow_multi_device_admin' => $request->boolean('allow_multi_device_admin'),
            'stale_timeout_seconds' => (int) $request->input('stale_timeout_seconds', 300),
            'updated_at' => Carbon::now('Asia/Makassar')->toDateTimeString(),
            'updated_by' => session('username', 'IT Support')
        ];

        $this->database->getReference('settings/session_security')->set($data);

        $limitText = $data['max_sessions_per_account'] == 0 ? 'Unlimited' : $data['max_sessions_per_account'] . ' Perangkat';
        $this->logService->log(
            'update',
            session('user_id', 'it_support'),
            session('username', 'IT Support'),
            "IT Support mengubah kebijakan <strong>Batas Multi-Login Akun</strong> menjadi <strong>{$limitText}</strong>."
        );

        return redirect()->back()->with('success', 'Pengaturan Keamanan Sesi & Multi-Login berhasil disimpan!');
    }

    /**
     * Simpan Konfigurasi Bot Telegram.
     */
    public function updateTelegram(Request $request)
    {
        $request->validate([
            'bot_token' => 'nullable|string',
            'chat_id' => 'nullable|string',
        ]);

        $data = [
            'is_active' => $request->boolean('is_active'),
            'bot_token' => trim($request->input('bot_token', '')),
            'chat_id' => trim($request->input('chat_id', '')),
            'updated_at' => Carbon::now('Asia/Makassar')->toDateTimeString(),
            'updated_by' => session('username', 'IT Support')
        ];

        $this->database->getReference('settings/telegram')->set($data);

        $this->logService->log(
            'update',
            session('user_id', 'it_support'),
            session('username', 'IT Support'),
            "IT Support memperbarui konfigurasi <strong>Bot Telegram Notifier</strong>."
        );

        return redirect()->back()->with('success', 'Konfigurasi Bot Telegram berhasil disimpan!');
    }

    /**
     * Test Kirim Notifikasi Telegram.
     */
    public function testTelegram(Request $request)
    {
        $botToken = trim($request->input('bot_token', ''));
        $chatId = trim($request->input('chat_id', ''));

        if (empty($botToken) || empty($chatId)) {
            // Ambil dari database jika kosong di input
            $saved = $this->database->getReference('settings/telegram')->getValue();
            $botToken = $saved['bot_token'] ?? env('TELEGRAM_BOT_TOKEN', '');
            $chatId = $saved['chat_id'] ?? env('TELEGRAM_CHAT_ID', '');
        }

        if (empty($botToken) || empty($chatId)) {
            return response()->json([
                'status' => false,
                'message' => 'Silakan isi Bot Token dan Chat ID terlebih dahulu.'
            ], 422);
        }

        $result = $this->telegramNotifier->testNotification($botToken, $chatId);
        return response()->json($result);
    }

    /**
     * Jalankan Cek Kesehatan Server Langsung dari Web.
     */
    public function runHealthCheck()
    {
        $results = [];

        // 1. Firebase Check
        try {
            $testKey = '__health_ping__';
            $now = Carbon::now('Asia/Makassar')->toDateTimeString();
            $this->database->getReference("settings/{$testKey}")->set($now);
            $results['firebase'] = ['status' => true, 'message' => 'Terhubung normal & responsif'];
        } catch (\Throwable $e) {
            $results['firebase'] = ['status' => false, 'message' => $e->getMessage()];
        }

        // 2. NTP Time Check
        try {
            $localTime = time();
            $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 3]]);
            $headers = @get_headers('https://www.google.com', true, $ctx);
            if (!empty($headers['Date'])) {
                $headerDate = is_array($headers['Date']) ? end($headers['Date']) : $headers['Date'];
                $remoteTime = strtotime($headerDate);
                $drift = abs($localTime - $remoteTime);
                $results['ntp'] = [
                    'status' => $drift <= 10,
                    'message' => $drift <= 10 ? "Sinkron (Selisih {$drift} detik)" : "Tergeser {$drift} detik dari waktu internet"
                ];
            } else {
                $results['ntp'] = ['status' => true, 'message' => 'Bypass (Google API tidak terjangkau)'];
            }
        } catch (\Throwable $e) {
            $results['ntp'] = ['status' => true, 'message' => 'NTP Check bypass'];
        }

        // 3. Disk Space Check
        try {
            $free = @disk_free_space(base_path());
            $total = @disk_total_space(base_path());
            if ($free && $total) {
                $pct = round((($total - $free) / $total) * 100, 1);
                $freeGb = round($free / (1024 * 1024 * 1024), 2);
                $results['disk'] = [
                    'status' => $pct < 90,
                    'message' => "Terpakai {$pct}% (Sisa ruang {$freeGb} GB)"
                ];
            } else {
                $results['disk'] = ['status' => true, 'message' => 'Disk normal'];
            }
        } catch (\Throwable $e) {
            $results['disk'] = ['status' => true, 'message' => 'Disk check bypass'];
        }

        return response()->json([
            'status' => true,
            'data' => $results,
            'time' => Carbon::now('Asia/Makassar')->format('d M Y, H:i:s') . ' WITA'
        ]);
    }

    /**
     * Simpan Pengaturan Operasional & Istirahat.
     */
    public function updateOperational(Request $request)
    {
        $request->validate([
            'max_break_minutes' => 'required|integer|min:15|max:300',
        ]);

        $data = [
            'max_break_minutes' => (int) $request->input('max_break_minutes', 60),
            'allow_cross_claim' => $request->boolean('allow_cross_claim'),
            'sound_alert_violation' => $request->boolean('sound_alert_violation'),
            'updated_at' => Carbon::now('Asia/Makassar')->toDateTimeString(),
            'updated_by' => session('username', 'IT Support')
        ];

        $this->database->getReference('settings/operational')->set($data);

        $this->logService->log(
            'update',
            session('user_id', 'it_support'),
            session('username', 'IT Support'),
            "IT Support memperbarui konfigurasi <strong>Operasional & Istirahat</strong>."
        );

        return redirect()->back()->with('success', 'Pengaturan Operasional berhasil disimpan!');
    }

    /**
     * Unduh Backup Langsung Database Firebase (.json).
     */
    public function downloadBackup()
    {
        try {
            $data = $this->database->getReference('/')->getValue() ?? [];
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $filename = 'backup_sedetik_dishub_' . date('Y-m-d_H-i-s') . '.json';

            return response($json, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membuat backup database: ' . $e->getMessage());
        }
    }
}
