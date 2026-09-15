<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use App\Services\TelegramNotifierService;
use Carbon\Carbon;

class ServerHealthCheckCommand extends Command
{
    protected $signature = 'server:health-check {--notify : Paksa kirim notifikasi ke Telegram jika ada masalah}';
    protected $description = 'Jalankan pemeriksaan kesehatan server proaktif (Firebase, Jam Sistem, Disk Space)';

    protected $database;
    protected $notifier;

    public function __construct(Database $database, TelegramNotifierService $notifier)
    {
        parent::__construct();
        $this->database = $database;
        $this->notifier = $notifier;
    }

    public function handle(): int
    {
        $this->info("=== MEMULAI HEALTH CHECK SERVER SEDETIK DISHUB ===");
        $issues = [];

        // 1. CEK KONEKSI FIREBASE REALTIME DATABASE
        $this->line("[1/3] Memeriksa koneksi Firebase Realtime Database...");
        try {
            $testKey = '__health_ping__';
            $nowTimestamp = Carbon::now('Asia/Makassar')->toDateTimeString();
            $this->database->getReference("settings/{$testKey}")->set($nowTimestamp);
            $readBack = $this->database->getReference("settings/{$testKey}")->getValue();

            if ($readBack === $nowTimestamp) {
                $this->info("  ✅ Firebase RTDB: Terhubung & Berfungsi Normal");
            } else {
                throw new \Exception("Gagal verifikasi data tulis/baca Firebase");
            }
        } catch (\Throwable $e) {
            $this->error("  ❌ Firebase RTDB: " . $e->getMessage());
            $issues[] = [
                'title' => 'Koneksi Firebase Database Gagal',
                'detail' => $e->getMessage(),
                'recommendation' => str_contains($e->getMessage(), 'invalid_grant')
                    ? 'Sinkronkan jam server dengan <code>sudo systemctl restart chrony</code> di VM.'
                    : 'Periksa file kredensial di <code>storage/app/firebase/</code> dan koneksi internet VM.'
            ];
        }

        // 2. CEK SINKRONISASI JAM SERVER (NTP CLOCK DRIFT)
        $this->line("[2/3] Memeriksa sinkronisasi jam sistem (NTP)...");
        try {
            $localTime = time();
            $remoteTime = null;

            // Ambil waktu dari Google HTTP Header (sangat cepat & stabil)
            $ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 3]]);
            $headers = @get_headers('https://www.google.com', true, $ctx);

            if (!empty($headers['Date'])) {
                $headerDate = is_array($headers['Date']) ? end($headers['Date']) : $headers['Date'];
                $remoteTime = strtotime($headerDate);
            }

            if ($remoteTime) {
                $driftSeconds = abs($localTime - $remoteTime);
                if ($driftSeconds > 10) {
                    $this->warn("  ⚠️ Jam Server Drift: Tergeser {$driftSeconds} detik dari waktu internet!");
                    $issues[] = [
                        'title' => 'Jam Server (NTP) Tidak Sinkron',
                        'detail' => "Jam server meleset {$driftSeconds} detik dari waktu dunia.",
                        'recommendation' => 'Jalankan <code>sudo timedatectl set-ntp on && sudo systemctl restart chrony</code> di Termius.'
                    ];
                } else {
                    $this->info("  ✅ Jam Server: Sinkron (Selisih {$driftSeconds}s)");
                }
            } else {
                $this->line("  ℹ️ Lewati cek NTP (Google API tidak terjangkau)");
            }
        } catch (\Throwable $e) {
            $this->line("  ℹ️ NTP Check bypass: " . $e->getMessage());
        }

        // 3. CEK KAPASITAS DISK STORAGE
        $this->line("[3/3] Memeriksa kapasitas penyimpanan disk...");
        try {
            $diskPath = base_path();
            $freeBytes = @disk_free_space($diskPath);
            $totalBytes = @disk_total_space($diskPath);

            if ($freeBytes !== false && $totalBytes !== false && $totalBytes > 0) {
                $usedPercentage = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 1);
                $freeGb = round($freeBytes / (1024 * 1024 * 1024), 2);

                if ($usedPercentage > 85) {
                    $this->warn("  ⚠️ Disk Hampir Penuh: Terpakai {$usedPercentage}% (Sisa {$freeGb} GB)");
                    $issues[] = [
                        'title' => 'Kapasitas Penyimpanan Disk Hampir Penuh',
                        'detail' => "Penggunaan disk mencapai {$usedPercentage}%. Sisa {$freeGb} GB.",
                        'recommendation' => 'Bersihkan file log lama di <code>storage/logs/</code> atau perbesar volume VM.'
                    ];
                } else {
                    $this->info("  ✅ Disk Storage: Normal ({$usedPercentage}% terpakai, sisa {$freeGb} GB)");
                }
            }
        } catch (\Throwable $e) {
            $this->line("  ℹ️ Disk check bypass");
        }

        $this->line("--------------------------------------------------");
        if (empty($issues)) {
            $this->info("🎉 KESEHATAN SERVER: 100% NORMAL. SEMUA SISTEM SIAP OPERASIONAL.");
            return Command::SUCCESS;
        } else {
            $this->error("⚠️ TERDETEKSI " . count($issues) . " KENDALA PADA SERVER!");
            
            // Otomatis kirim laporan ke Telegram jika ada issue
            $this->notifier->reportHealthIssue($issues);
            $this->line("📲 Notifikasi peringatan telah dikirim ke Telegram IT Support.");
            return Command::FAILURE;
        }
    }
}
