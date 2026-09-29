<?php

namespace App\Console\Commands;

use App\Services\PenugasanStatusService;
use App\Support\PenugasanWaktu;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Menonaktifkan penugasan yang sudah melewati waktu selesai.
 *
 * Command ini adalah versi otomatis dari blok "AUTOMATIC CLEANUP" yang
 * sebelumnya hanya berjalan saat admin membuka halaman dashboard (lihat
 * AdminController::index). Tanpa penjadwal, field `status` bisa basi
 * (stale) selamanya sehingga penugasan lama tetap terbaca aktif.
 *
 * Penentuan siapa yang kedaluwarsa TIDAK ada di file ini. Aturan itu milik
 * `App\Support\PenugasanWaktu` dan ditulis lewat
 * `App\Services\PenugasanStatusService`, jadi command ini hanya mengatur
 * tampilan, bukan keputusan.
 */
class NonaktifkanPenugasanCommand extends Command
{
    protected $signature = 'penugasan:nonaktifkan
                            {--dry-run : Tampilkan kandidat yang akan diubah tanpa menulis ke database}';

    protected $description = 'Set status penugasan menjadi inaktif bila waktu selesai sudah terlewati';

    protected $penugasanStatus;

    public function __construct(PenugasanStatusService $penugasanStatus)
    {
        parent::__construct();
        $this->penugasanStatus = $penugasanStatus;
    }

    public function handle(): int
    {
        $now = Carbon::now('Asia/Makassar');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== NONAKTIFKAN PENUGASAN KEDALUWARSA ===');
        $this->line('Waktu acuan (' . PenugasanWaktu::ZONA_WAKTU . '): ' . $now->toDateTimeString());
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: tidak ada perubahan yang ditulis ke database.');
        }
        $this->newLine();

        // Command ini tidak lagi menentukan sendiri siapa yang kedaluwarsa.
        // Aturan itu milik App\Support\PenugasanWaktu, supaya tidak
        // berbeda dengan halaman lain.
        try {
            $kandidat = $this->penugasanStatus->kandidatKedaluwarsa($now);
        } catch (\Throwable $e) {
            $this->error('Gagal membaca data penugasan: ' . $e->getMessage());
            return self::FAILURE;
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

        foreach ($kandidat as $keyTugas => $item) {
            $tugas   = $item[PenugasanWaktu::KUNCI_TUGAS];
            $idLokasi = $tugas['id_lokasi'] ?? '-';
            $baris = sprintf(
                '  - [%s] lokasi=%s selesai=%s',
                $keyTugas,
                $idLokasi,
                $item['selesai']->toDateTimeString()
            );

            if ($dryRun) {
                $this->line($baris . '  (akan diubah)');
                continue;
            }

            try {
                $this->penugasanStatus->tulisStatus((string) $keyTugas, PenugasanWaktu::STATUS_NONAKTIF);
                $this->info($baris . '  -> ' . PenugasanWaktu::STATUS_NONAKTIF);
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
