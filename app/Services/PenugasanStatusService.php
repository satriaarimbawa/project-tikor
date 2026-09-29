<?php

namespace App\Services;

use App\Support\PenugasanWaktu;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;

/**
 * Satu-satunya tempat yang menulis field `penugasan/{id}/status` otomatis.
 *
 * Duluan logika ini ada di dua tempat sekaligus:
 *   * AdminController::index, blok "AUTOMATIC CLEANUP" yang hanya jalan
 *     kebetulan saat admin membuka dashboard, dan
 *   * NonaktifkanPenugasanCommand, hasil refactor sebelumnya.
 *
 * Keduanya harusnya selalu stumbling pada aturan yang sama. Karena itu
 * sekarang mereka memanggil kelas ini.
 *
 * Yang ditulis hanya satu field: `status` jadi 'inaktif'. Data
 * `survei_harian` dan `laporan_harian` tidak disentuh, jadi Aman
 * dijalankan berulang kali.
 */
class PenugasanStatusService
{
    /** Koneksi Firebase Realtime Database. */
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Daftar penugasan yang perlu dinonaktifkan, tanpa menulis apa pun.
     *
     * Kandidat = masih berstatus 'aktif' DAN waktu selesai sudah terlewati.
     * Penugasan tanpa waktu selesai yang valid dilewati, bukan dianggap
     * kedaluwarsa, supaya data rusak tidak dihapus statusnya.
     *
     * @return array<string, array{key: string, tugas: array, selesai: Carbon}>
     */
    public function kandidatKedaluwarsa(?Carbon $now = null): array
    {
        $now     = $now ?? PenugasanWaktu::sekarang();
        $semua   = $this->bacaPenugasan();
        $kandidat = [];

        foreach ($semua as $keyTugas => $tugas) {
            if (!PenugasanWaktu::masihAktif($tugas)) {
                continue;
            }

            $selesai = PenugasanWaktu::selesai($tugas);
            if ($selesai === null) {
                continue;
            }

            if ($now->gt($selesai)) {
                $kandidat[$keyTugas] = [
                    PenugasanWaktu::KUNCI_KEY   => (string) $keyTugas,
                    PenugasanWaktu::KUNCI_TUGAS => $tugas,
                    'selesai'                   => $selesai,
                ];
            }
        }

        return $kandidat;
    }

    /**
     * Tandai semua penugasan kedaluwarsa menjadi 'inaktif'.
     *
     * Aman dipanggil sesering mungkin: yang sudah 'inaktif' dilewati, dan
     * mode dry-run tidak menulis apa pun ke database.
     *
     * @param Carbon|null $now     Waktu acuan (default: sekarang)
     * @param bool        $dryRun  true = hanya kumpulkan kandidat, jangan tulis
     * @return array{kandidat: int, berhasil: int, gagal: int, detail: array<int, array{key: string, pesan: string}>}
     */
    public function nonaktifkanKedaluwarsa(?Carbon $now = null, bool $dryRun = false): array
    {
        $kandidat = $this->kandidatKedaluwarsa($now);
        $hasil    = [
            'kandidat' => count($kandidat),
            'berhasil' => 0,
            'gagal'    => 0,
            'detail'   => [],
        ];

        if ($kandidat === [] || $dryRun) {
            return $hasil;
        }

        foreach ($kandidat as $keyTugas => $item) {
            try {
                $this->tulisStatus((string) $keyTugas, PenugasanWaktu::STATUS_NONAKTIF);
                $hasil['berhasil']++;
            } catch (\Throwable $e) {
                $hasil['gagal']++;
                $hasil['detail'][] = [
                    'key'   => (string) $keyTugas,
                    'pesan' => $e->getMessage(),
                ];
            }
        }

        return $hasil;
    }

    /**
     * Tulis field status satu penugasan.
     */
    public function tulisStatus(string $keyTugas, string $status): void
    {
        $this->database
            ->getReference('penugasan/' . $keyTugas . '/status')
            ->set($status);
    }

    /**
     * Baca seluruh node `penugasan`, dengan hasil selalu array.
     */
    protected function bacaPenugasan(): array
    {
        $data = $this->database->getReference('penugasan')->getValue() ?? [];

        return is_array($data) ? $data : [];
    }
}
