<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Satu-satunya sumber kebenaran untuk pertanyaan
 * "apakah penugasan ini sedang berjalan?".
 *
 * Sebelumnya aturan ini ditulis ulang di 8 tempat (OperatorController x4,
 * LoginController x2, LiveDashboardController x2, TikorController,
 * AdminController, dan command penjadwal) dengan sedikit perbedaan tiap
 * tempat. Akibatnya Live Dashboard pernah menampilkan lokasi yang tidak
 * sedang diuji, sedangkan halaman operator memakai aturan lain.
 * Semua panggilan harus diarahkan ke kelas ini.
 *
 * ATURAN:
 *   sedang berjalan = waktu_mulai <= sekarang <= waktu_selesai
 *                    DAN status penugasan bukan 'inaktif'
 *
 * Kenapa dua syarat?
 *
 * - Syarat WAKTU adalah sumber kebenaran utama. Field `status` pernah basi
 *   (hanya ditulis saat admin membuka dashboard), sehingga penugasan yang
 *   sudah lewat waktu harus tetap dianggap berhenti walau status masih
 *   'aktif'. Syarat ini juga aman walau penjadwal belum terpasang.
 *
 * - Syarat STATUS menghormati penghentian manual. Ada 3 pihak yang menulis
 *   'inaktif' tanpa menunggu waktu habis:
 *       * PenugasanController::resetStatus  (admin me-reset jadwal)
 *       * LoginController                  (operator di luar radius geofence)
 *       * PenugasanStatusService            (otomatis, waktu sudah habis)
 *   Penugasan seperti itu harus berhenti walaupun waktunya belum habis.
 *
 * Hanya ada dua nilai status yang sah: 'aktif' dan 'inaktif'
 * (lihat PenugasanController::simpan dan ::resetStatus).
 * Penugasan tanpa field 'status' diperlakukan sebagai 'aktif' supaya
 * penyaringan tetap mengandalkan waktu, bukan kelengkapan data.
 *
 * Semua perbandingan memakai zona waktu Asia/Makassar (WITA) karena itu
 * zona tempat penugasan dibuat di input form.
 */
final class PenugasanWaktu
{
    /** Zona waktu seluruh jam penugasan. */
    public const ZONA_WAKTU = 'Asia/Makassar';

    /** Penugasan masih berjalan, kecuali sudah dihentikan manual. */
    public const STATUS_AKTIF = 'aktif';

    /** Penugasan dihentikan manual atau sudah lewat waktu selesai. */
    public const STATUS_NONAKTIF = 'inaktif';

    /**
     * Alasan berhenti yang mungkin dikembalikan `alasanBerhenti()`.
     */
    public const ALASAN_WAKTU = 'waktu';
    public const ALASAN_DIHENTIKAN = 'dihentikan';

    /** Kunci array hasil `cari()` dan `semua()`. */
    public const KUNCI_KEY = 'key';
    public const KUNCI_TUGAS = 'tugas';

    /** Kelas utilitas, tidak boleh diinstansiasi. */
    private function __construct()
    {
    }

    /**
     * Waktu acuan saat ini dalam zona WITA.
     */
    public static function sekarang(): Carbon
    {
        return Carbon::now(self::ZONA_WAKTU);
    }

    /**
     * Waktu mulai penugasan, atau null bila kosong/tidak bisa diparse.
     */
    public static function mulai(array $tugas): ?Carbon
    {
        return self::waktuDari($tugas, 'waktu_mulai');
    }

    /**
     * Waktu selesai penugasan, atau null bila kosong/tidak bisa diparse.
     */
    public static function selesai(array $tugas): ?Carbon
    {
        return self::waktuDari($tugas, 'waktu_selesai');
    }

    /**
     * Status penugasan. Data tanpa field status dianggap 'aktif'.
     */
    public static function status(array $tugas): string
    {
        $status = $tugas['status'] ?? self::STATUS_AKTIF;
        return is_string($status) ? $status : self::STATUS_AKTIF;
    }

    /**
     * Apakah penugasan belum dihentikan manual.
     *
     * Dipakai command penjadwal: yang perlu dinonaktifkan hanya penugasan
     * yang masih 'aktif' lalu sudah lewat waktu. Yang sudah 'inaktif'
     * tidak boleh ditulis ulang (percuma menambah operasi tulis).
     */
    public static function masihAktif(array $tugas): bool
    {
        return self::status($tugas) !== self::STATUS_NONAKTIF;
    }

    /**
     * Apakah waktu selesai sudah terlewati.
     *
     * Ini murni perbandingan waktu, tanpa melihat status. Penugasan yang
     * sudah dihentikan manual juga boleh ikut true; pemanggil yang
     * memutuskan.
     */
    public static function sudahKedaluwarsa(array $tugas, ?Carbon $now = null): bool
    {
        $now  = $now ?? self::sekarang();
        $selesai = self::selesai($tugas);

        return $selesai !== null && $now->gt($selesai);
    }

    /**
     * Alasan penugasan tidak berjalan, atau null bila sedang berjalan.
     *
     * Urutan pemeriksaan sengaja: WAKTU dulu, lalu STATUS. Urutan ini
     * dipertahankan supaya pesan error yang tampil di halaman login tidak
     * berubah (penugasan yang dihentikan tetapi waktunya belum tiba dulu
     * dilewati diam-diam, tidak memicu pesan "sudah dihentikan").
     *
     * @return string|null self::ALASAN_WAKTU, self::ALASAN_DIHENTIKAN, atau null
     */
    public static function alasanBerhenti(array $tugas, ?Carbon $now = null): ?string
    {
        $now    = $now ?? self::sekarang();
        $mulai  = self::mulai($tugas);
        $selesai = self::selesai($tugas);

        // Tanpa kedua batas waktu, penugasan tidak bisa dinilai.
        if ($mulai === null || $selesai === null) {
            return self::ALASAN_WAKTU;
        }

        if (!$now->between($mulai, $selesai)) {
            return self::ALASAN_WAKTU;
        }

        if (!self::masihAktif($tugas)) {
            return self::ALASAN_DIHENTIKAN;
        }

        return null;
    }

    /**
     * Apakah penugasan sedang berjalan. Membungkus `alasanBerhenti()`.
     */
    public static function sedangBerjalan(array $tugas, ?Carbon $now = null): bool
    {
        return self::alasanBerhenti($tugas, $now) === null;
    }

    /**
     * Daftar id lokasi yang sedang diuji, unik dan tanpa nilai kosong.
     *
     * Dipakai Live Dashboard dan halaman daftar lokasi TIKOR.
     *
     * @param array $semua Data node `penugasan` dari Firebase
     * @return array<int, string>
     */
    public static function idLokasiBerjalan(array $semua, ?Carbon $now = null): array
    {
        $now = $now ?? self::sekarang();
        $ids = [];

        foreach (self::berkasValid($semua) as $tugas) {
            if (!self::sedangBerjalan($tugas, $now)) {
                continue;
            }
            $idLokasi = (string) ($tugas['id_lokasi'] ?? '');
            if ($idLokasi !== '') {
                $ids[] = $idLokasi;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Cari satu penugasan yang sedang berjalan dan lolos filter.
     *
     * @param array         $semua  Data node `penugasan` dari Firebase
     * @param callable|null $filter fn(array $tugas, string $key): bool
     * @return array{key: string, tugas: array}|null
     */
    public static function cari(array $semua, ?callable $filter = null, ?Carbon $now = null): ?array
    {
        $hasil = self::semua($semua, $filter, $now, 1);

        return $hasil[0] ?? null;
    }

    /**
     * Semua penugasan yang sedang berjalan dan lolos filter.
     *
     * @param array         $semua   Data node `penugasan` dari Firebase
     * @param callable|null $filter  fn(array $tugas, string $key): bool
     * @param Carbon|null   $now     Waktu acuan
     * @param int|null      $batas   Batasi jumlah hasil (null = tanpa batas)
     * @return array<int, array{key: string, tugas: array}>
     */
    public static function semua(array $semua, ?callable $filter = null, ?Carbon $now = null, ?int $batas = null): array
    {
        $now   = $now ?? self::sekarang();
        $hasil = [];

        foreach (self::berkasValid($semua) as $key => $tugas) {
            if (!self::sedangBerjalan($tugas, $now)) {
                continue;
            }
            if ($filter !== null && !$filter($tugas, (string) $key)) {
                continue;
            }

            $hasil[] = [
                self::KUNCI_KEY   => (string) $key,
                self::KUNCI_TUGAS => $tugas,
            ];

            if ($batas !== null && count($hasil) >= $batas) {
                break;
            }
        }

        return $hasil;
    }

    /**
     * Nama lokasi tempat user sedang ditugaskan, atau null bila tidak ada.
     *
     * Dipakai Live Dashboard untuk label lokasi tiap operator.
     *
     * @param array $semua    Data node `penugasan` dari Firebase
     * @param array $petaNama id lokasi => nama lokasi
     */
    public static function namaLokasiAktif(array $semua, string $userId, array $petaNama, ?Carbon $now = null): ?string
    {
        $cocok = self::cari(
            $semua,
            static fn (array $tugas): bool => (string) ($tugas['id_user'] ?? '') === $userId,
            $now
        );

        if ($cocok === null) {
            return null;
        }

        $idLokasi = (string) ($cocok[self::KUNCI_TUGAS]['id_lokasi'] ?? '');

        return $petaNama[$idLokasi] ?? null;
    }

    /**
     * Daftar objek survei penugasan sebagai array nilai yang sudah dirapikan.
     *
     * Field `objek_survei` disimpan sebagai satu string dipisah koma
     * (mis. "minibus,pick-up"). Nilai kosong dan spasi excess dibuang.
     *
     * @return array<int, string>
     */
    public static function objekSurvei(array $tugas): array
    {
        $raw = $tugas['objek_survei'] ?? '';

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * Buang entri yang bukan array (data rusak) sebelum diproses.
     *
     * @return array<string, array>
     */
    private static function berkasValid(array $semua): array
    {
        $bersih = [];

        foreach ($semua as $key => $tugas) {
            if (is_array($tugas)) {
                $bersih[(string) $key] = $tugas;
            }
        }

        return $bersih;
    }

    /**
     * Parse satu field waktu menjadi Carbon, atau null bila tidak valid.
     *
     * Field waktu disimpan sebagai teks `Y-m-d H:i:s` (lihat
     * PenugasanController::simpan), tapi data lama dari form lain bisa
     * berformat beda, jadi parser tetap dipakai. Teks sampah harus
     * ditolak tanpa memunculkan warning PHP: strtotime mengembalikan
     * false untuk teks yang tidak bisa dibaca, sedangkan Carbon::parse
     * memunculkan warning "Failed to parse time string" dulu sebelum
     * melempar exception. Pengecekan awal lewat strtotime mencegah hal itu.
     */
    private static function waktuDari(array $tugas, string $field): ?Carbon
    {
        $nilai = $tugas[$field] ?? null;

        if ($nilai === null) {
            return null;
        }

        // Angka (Unix timestamp) langsung diserahkan ke Carbon, sama
        // seperti perilaku kode lama sebelum kelas ini ada.
        if (is_int($nilai) || is_float($nilai)) {
            return self::parseAman($nilai);
        }

        if (!is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        if ($nilai === '' || strtotime($nilai) === false) {
            return null;
        }

        return self::parseAman($nilai);
    }

    /**
     * Panggil Carbon::parse dan swallow exception-nya.
     *
     * @param mixed $nilai
     */
    private static function parseAman($nilai): ?Carbon
    {
        try {
            return Carbon::parse($nilai, self::ZONA_WAKTU);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
