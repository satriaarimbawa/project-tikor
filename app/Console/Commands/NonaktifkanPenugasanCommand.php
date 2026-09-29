<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

/**
 * Menonaktifkan penugasan yang sudah melewati waktu selesai.
 *
 * Command ini adalah versi otomatis dari blok "AUTOMATIC CLEANUP" yang
 * sebelumnya hanya berjalan saat admin membuka halaman dashboard (lihat
 * AdminController::dashboard). Tanpa penjadwal, field `status` bisa basi
 * (stale) selamanya sehingga penugasan lama tetap terbaca aktif.
 *
 * JANGAN memakai field `status` sebagai sumber kebenaran di mana pun.
 * Penentuan "sedang berjalan" selalu dari perbandingan waktu:
 *     waktu_mulai < sekarang < waktu_selesai
 */
class NonaktifkanPenugasanCommand extends Command
{
    protected $signature = 'penugasan:nonaktifkan
                            {--dry-run : Tampilkan kandidat yang akan diubah tanpa menulis ke database}';

    protected $description = 'Set status penugasan menjadi inaktif bila waktu selesai sudah terlewati';

    protected $database;

    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function handle(): int
    {
        $now = Carbon::now('Asia/Makassar');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== NONAKTIFKAN PENUGASAN KEDALUWARSA ===');
        $this->line('Waktu acuan (Asia/Makassar): ' . $now->toDateTimeString());
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: tidak ada perubahan yang ditulis ke database.');
        }
        $this->newLine();

        try {
            $semuaTugas = $this->database->getReference('penugasan')->getValue() ?? [];
        } catch (\Throwable $e) {
            $this->error('Gagal membaca data penugasan: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (empty($semuaTugas)) {
            $this->info('Tidak ada data penugasan.');
            return self::SUCCESS;
        }

        $kandidat = [];
        foreach ($semuaTugas as $keyTugas => $t) {
            if (!is_array($t)) {
                continue;
            }
            // Hanya yang masih aktif dan punya waktu selesai yang valid.
            if (($t['status'] ?? 'inaktif') !== 'aktif') {
                continue;
            }
            if (empty($t['waktu_selesai'])) {
                continue;
            }

            try {
                $selesai = Carbon::parse($t['waktu_selesai'], 'Asia/Makassar');
            } catch (\Throwable $e) {
                $this->warn('Melewati penugasan ' . $keyTugas . ': waktu_selesai tidak valid.');
                continue;
            }

            if ($now->gt($selesai)) {
                $kandidat[$keyTugas] = $t;
            }
        }

        $jumlah = count($kandidat);

        if ($jumlah === 0) {
            $this->info('Tidak ada penugasan aktif yang sudah melewati waktu selesai.');
            return self::SUCCESS;
        }

        $this->info('Ditemukan ' . $jumlah . ' penugasan aktif yang sudah kedaluwarsa:');
        $this->newLine();

        $berhasil = 0;
        $gagal = 0;

        foreach ($kandidat as $keyTugas => $t) {
            $idLokasi = $t['id_lokasi'] ?? '-';
            $selesai = Carbon::parse($t['waktu_selesai'], 'Asia/Makassar');
            $baris = sprintf(
                '  - [%s] lokasi=%s selesai=%s',
                $keyTugas,
                $idLokasi,
                $selesai->toDateTimeString()
            );

            if ($dryRun) {
                $this->line($baris . '  (akan diubah)');
                continue;
            }

            try {
                $this->database->getReference('penugasan/' . $keyTugas . '/status')->set('inaktif');
                $this->info($baris . '  -> inaktif');
                $berhasil++;
            } catch (\Throwable $e) {
                $this->error($baris . '  GAGAL: ' . $e->getMessage());
                $gagal++;
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info('DRY-RUN selesai. Jalankan tanpa --dry-run untuk menerapkan perubahan.');
        } else {
            $this->info('Selesai. Berhasil: ' . $berhasil . ', Gagal: ' . $gagal . '.');
        }

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
