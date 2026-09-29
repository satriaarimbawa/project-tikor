<?php

namespace Tests\Unit;

use App\Support\PenugasanWaktu;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test untuk aturan tunggal "apakah penugasan sedang berjalan".
 *
 * Ini regression test untuk dua bug yang berbeda akar:
 *
 * 1. Live Dashboard menampilkan semua lokasi, bukan hanya yang sedang
 *    diuji, karena penentuannya hanya melihat waktu tanpa memfilter
 *    penugasan yang sudah dihentikan admin.
 *
 * 2. Field `status` pernah basi karena hanya ditulis saat admin membuka
 *    dashboard. Penugasan yang lewat waktu masih terbaca 'aktif'.
 *
 * Aturan yang diuji:
 *   sedang berjalan = waktu_mulai <= sekarang <= waktu_selesai
 *                    DAN status bukan 'inaktif'
 */
class PenugasanWaktuTest extends TestCase
{
    private const ACUAN = '2026-03-10 10:00:00';

    private function now(): Carbon
    {
        return Carbon::parse(self::ACUAN, PenugasanWaktu::ZONA_WAKTU);
    }

    /**
     * @param array $ubah Nilai penugasan yang menimpa nilai bawaan.
     */
    private function tugas(array $ubah = []): array
    {
        return array_merge([
            'id_lokasi'    => 'L01',
            'id_user'      => 'u1',
            'status'       => 'aktif',
            'waktu_mulai'  => '2026-03-10 08:00:00',
            'waktu_selesai'=> '2026-03-10 12:00:00',
            'objek_survei' => 'motor,minibus',
        ], $ubah);
    }

    // ---------------------------------------------------------------
    // Aturan utama: sedangBerjalan
    // ---------------------------------------------------------------

    #[Test]
    public function di_dalam_jendela_waktu_berjalan(): void
    {
        $this->assertTrue(
            PenugasanWaktu::sedangBerjalan($this->tugas(), $this->now())
        );
    }

    #[Test]
    public function status_basi_aktif_tetap_berhenti_setelah_waktu_selesai(): void
    {
        // Status masih 'aktif' karena penjadwal belum pernah jalan.
        // Waktu sudah lewat, jadi harus tetap dianggap berhenti.
        $tugas = $this->tugas();

        $this->assertSame('aktif', PenugasanWaktu::status($tugas));
        $this->assertFalse(
            PenugasanWaktu::sedangBerjalan($tugas, Carbon::parse('2026-03-10 12:00:01', PenugasanWaktu::ZONA_WAKTU))
        );
    }

    #[Test]
    public function status_inaktif_dihentikan_admin_berhenti_walau_waktu_masih_panas(): void
    {
        $tugas = $this->tugas(['status' => 'inaktif']);

        $this->assertFalse(
            PenugasanWaktu::sedangBerjalan($tugas, $this->now()),
            'Penugasan yang sudah dihentikan manual tidak boleh jalan lagi.'
        );
    }

    #[Test]
    public function belum_mulai_berhenti(): void
    {
        $tugas = $this->tugas([
            'waktu_mulai'   => '2026-03-10 11:00:00',
            'waktu_selesai' => '2026-03-10 12:00:00',
        ]);

        $this->assertFalse(PenugasanWaktu::sedangBerjalan($tugas, $this->now()));
    }

    #[Test]
    public function batas_tepat_dihitung_masih_berjalan(): void
    {
        // Batas bersifat inklusif, sama dengan Carbon::between() yang
        // dipakai kode lama. Jangan sampai ketiak tanpa sengaja.
        $mulai   = Carbon::parse('2026-03-10 08:00:00', PenugasanWaktu::ZONA_WAKTU);
        $selesai = Carbon::parse('2026-03-10 12:00:00', PenugasanWaktu::ZONA_WAKTU);

        $this->assertTrue(PenugasanWaktu::sedangBerjalan($this->tugas(), $mulai));
        $this->assertTrue(PenugasanWaktu::sedangBerjalan($this->tugas(), $selesai));
    }

    #[Test]
    public function tanpa_waktu_selesai_diperlakukan_berhenti(): void
    {
        // Kode lama memanggil Carbon::parse(null) yang hasilnya waktu
        // SEKARANG, sehingga penugasan rusak ikut terbaca berjalan.
        $tugas = $this->tugas(['waktu_selesai' => null]);

        $this->assertFalse(PenugasanWaktu::sedangBerjalan($tugas, $this->now()));
        $this->assertNull(PenugasanWaktu::selesai($tugas));
    }

    #[Test]
    public function tanggal_tidak_valid_diperlakukan_berhenti(): void
    {
        $tugas = $this->tugas(['waktu_selesai' => 'bukan tanggal']);

        $this->assertFalse(PenugasanWaktu::sedangBerjalan($tugas, $this->now()));
        $this->assertNull(PenugasanWaktu::selesai($tugas));
    }

    #[Test]
    public function tanpa_field_status_dianggap_aktif(): void
    {
        $tugas = $this->tugas();
        unset($tugas['status']);

        $this->assertSame('aktif', PenugasanWaktu::status($tugas));
        $this->assertTrue(PenugasanWaktu::sedangBerjalan($tugas, $this->now()));
    }

    // ---------------------------------------------------------------
    // alasanBerhenti
    // ---------------------------------------------------------------

    #[Test]
    public function alasan_berhenti_membeda_waktu_dan_penghentian(): void
    {
        $dalamJendela = $this->now();

        $this->assertNull(PenugasanWaktu::alasanBerhenti($this->tugas(), $dalamJendela));
        $this->assertSame(
            PenugasanWaktu::ALASAN_DIHENTIKAN,
            PenugasanWaktu::alasanBerhenti($this->tugas(['status' => 'inaktif']), $dalamJendela)
        );
        $this->assertSame(
            PenugasanWaktu::ALASAN_WAKTU,
            PenugasanWaktu::alasanBerhenti($this->tugas(), Carbon::parse('2026-03-10 15:00:00', PenugasanWaktu::ZONA_WAKTU))
        );
    }

    #[Test]
    public function waktu_diperiksa_sebelum_status(): void
    {
        // Penugasan dihentikan tetapi waktunya belum tiba: harus kena
        // alasan WAKTU, bukan DIHENTIKAN. Halaman login bergantung pada
        // urutan ini untuk memilih pesan error.
        $tugas = $this->tugas([
            'status'        => 'inaktif',
            'waktu_mulai'   => '2026-03-10 11:00:00',
            'waktu_selesai' => '2026-03-10 12:00:00',
        ]);

        $this->assertSame(
            PenugasanWaktu::ALASAN_WAKTU,
            PenugasanWaktu::alasanBerhenti($tugas, $this->now())
        );
    }

    // ---------------------------------------------------------------
    // Kumpulan penugasan
    // ---------------------------------------------------------------

    #[Test]
    public function id_lokasi_berjalan_unik_dan_tanpa_kosong(): void
    {
        $semua = [
            'a' => $this->tugas(['id_lokasi' => 'L01']),
            'b' => $this->tugas(['id_lokasi' => 'L01']),   // lokasi sama
            'c' => $this->tugas(['id_lokasi' => 'L02']),
            'd' => $this->tugas(['id_lokasi' => 'L03', 'waktu_selesai' => '2026-03-10 09:00:00']), // sudah lewat
            'e' => $this->tugas(['id_lokasi' => '']),        // id kosong
        ];

        $this->assertSame(['L01', 'L02'], PenugasanWaktu::idLokasiBerjalan($semua, $this->now()));
    }

    #[Test]
    public function entri_bukan_array_dilewati(): void
    {
        $semua = [
            'a' => $this->tugas(['id_lokasi' => 'L01']),
            'b' => 'bukan array',
            'c' => null,
            'd' => 42,
        ];

        $this->assertSame(['L01'], PenugasanWaktu::idLokasiBerjalan($semua, $this->now()));
    }

    #[Test]
    public function cari_mengembalikan_key_dan_tugas(): void
    {
        $semua = [
            'ab12' => $this->tugas(['id_lokasi' => 'L09', 'status' => 'inaktif']),
            'cd34' => $this->tugas(['id_lokasi' => 'L01']),
        ];

        $hasil = PenugasanWaktu::cari(
            $semua,
            static fn (array $t): bool => ($t['id_lokasi'] ?? '') === 'L01',
            $this->now()
        );

        $this->assertNotNull($hasil);
        $this->assertSame('cd34', $hasil[PenugasanWaktu::KUNCI_KEY]);
        $this->assertSame('L01', $hasil[PenugasanWaktu::KUNCI_TUGAS]['id_lokasi']);
    }

    #[Test]
    public function cari_tidak_menemukan_kembalikan_null(): void
    {
        $semua = ['ab12' => $this->tugas(['status' => 'inaktif'])];

        $this->assertNull(PenugasanWaktu::cari($semua, null, $this->now()));
    }

    #[Test]
    public function semua_membatasi_jumlah(): void
    {
        $semua = [
            'a' => $this->tugas(['id_user' => 'u1']),
            'b' => $this->tugas(['id_user' => 'u1']),
            'c' => $this->tugas(['id_user' => 'u1']),
        ];

        $semua = PenugasanWaktu::semua(
            $semua,
            static fn (array $t): bool => ($t['id_user'] ?? '') === 'u1',
            $this->now(),
            2
        );

        $this->assertCount(2, $semua);
    }

    #[Test]
    public function nama_lokasi_aktif_ikut_menghormati_penghentian(): void
    {
        $petaNama = ['L01' => 'Bundaran'];
        $semua = [
            'a' => $this->tugas(['id_lokasi' => 'L01', 'status' => 'inaktif']),
            'b' => $this->tugas(['id_lokasi' => 'L02']),
        ];

        $this->assertNull(PenugasanWaktu::namaLokasiAktif($semua, 'u1', $petaNama, $this->now()));

        $semua['a']['status'] = 'aktif';
        $this->assertSame('Bundaran', PenugasanWaktu::namaLokasiAktif($semua, 'u1', $petaNama, $this->now()));

        $this->assertNull(PenugasanWaktu::namaLokasiAktif($semua, 'orang_lain', $petaNama, $this->now()));
    }

    // ---------------------------------------------------------------
    // Bantu: kedaluwarsa dan objek survei
    // ---------------------------------------------------------------

    #[Test]
    public function sudah_kedaluwarsa_hanya_bila_waktu_selesai_terlewati(): void
    {
        $sebelumSelesai = Carbon::parse('2026-03-10 11:59:59', PenugasanWaktu::ZONA_WAKTU);
        $setelahSelesai = Carbon::parse('2026-03-10 12:00:01', PenugasanWaktu::ZONA_WAKTU);

        $this->assertFalse(PenugasanWaktu::sudahKedaluwarsa($this->tugas(), $sebelumSelesai));
        $this->assertFalse(PenugasanWaktu::sudahKedaluwarsa($this->tugas(), $this->now()));
        $this->assertTrue(PenugasanWaktu::sudahKedaluwarsa($this->tugas(), $setelahSelesai));
    }

    #[Test]
    public function sudah_kedaluwarsa_mengabaikan_status(): void
    {
        // Dipakai penjadwal, yang memang butuh tahu waktu sudah lewat
        // tanpa peduli status sekarang. Yang diukur adalah waktu lewat,
        // jadi acuan waktu harus lewat waktu selesai.
        $setelahSelesai = Carbon::parse('2026-03-10 12:00:01', PenugasanWaktu::ZONA_WAKTU);
        $tugas = $this->tugas(['status' => 'inaktif']);

        $this->assertTrue(PenugasanWaktu::sudahKedaluwarsa($tugas, $setelahSelesai));
        $this->assertFalse(PenugasanWaktu::sudahKedaluwarsa($tugas, $this->now()));
    }

    #[Test]
    public function masih_aktif_benarkah_bukan_dihentikan(): void
    {
        $this->assertTrue(PenugasanWaktu::masihAktif($this->tugas()));
        $this->assertFalse(PenugasanWaktu::masihAktif($this->tugas(['status' => 'inaktif'])));
    }

    #[Test]
    public function objek_survei_diparse_dari_string_koma(): void
    {
        $this->assertSame(['motor', 'minibus'], PenugasanWaktu::objekSurvei($this->tugas()));
        $this->assertSame(['motor', 'minibus'], PenugasanWaktu::objekSurvei($this->tugas(['objek_survei' => ' motor , minibus '])));
        $this->assertSame([], PenugasanWaktu::objekSurvei($this->tugas(['objek_survei' => ''])));
        $this->assertSame([], PenugasanWaktu::objekSurvei($this->tugas(['objek_survei' => null])));
        $this->assertSame([], PenugasanWaktu::objekSurvei([]));
    }
}
