<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Pengujian untuk perbaikan "motor hilang" di Live Dashboard.
 *
 * Gejalanya: volume kendaraan yang ADA di `survei_harian` tidak pernah
 * muncul di layar kalau kunci field-nya tidak dimiliki satu pun objek di
 * master `objek_tarif`. Penyebabnya, `$validKeys` dibangun hanya dari
 * master, lalu seluruh penghitungan hanya mengiterasi `$validKeys`.
 *
 * Yang diuji di sini:
 *   1. data motor tanpa master -> tetap dihitung, ditandai, harga 0
 *   2. masternormal            -> perilaku lama, tidak berubah sama sekali
 *   3. kunci gabungan          -> tidak boleh terhitung dua kali
 *   4. data kemarin            -> tidak boleh bocor ke dashboard hari ini
 */
class LiveDashboardUnmappedTest extends TestCase
{
    /** Master objek tarif, ditimpa per test. */
    protected array $tarif = [];

    /** Data `survei_harian`, ditimpa per test. */
    protected array $survei = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(Database::class, function (MockInterface $mock) {
            $mock->shouldReceive('getReference')->andReturnUsing(function ($path) {
                $ref = \Mockery::mock('Kreait\Firebase\Database\Reference');

                if ($path === 'objek_tarif') {
                    $nilai = $this->tarif;
                } elseif ($path === 'survei_harian') {
                    $nilai = $this->survei;
                } elseif ($path === 'lokasi') {
                    $nilai = [
                        'lok1' => ['nama_lokasi' => 'Lokasi Satu', 'target_harian' => 50000],
                    ];
                } elseif ($path === 'penugasan') {
                    $nilai = $this->penugasanBerjalan();
                } elseif ($path === 'users') {
                    $nilai = [
                        'u1' => ['username' => 'Operator Satu', 'role_user' => 'operator'],
                    ];
                } else {
                    $nilai = null;
                }

                $ref->shouldReceive('getValue')->andReturn($nilai);

                return $ref;
            });
        });
    }

    /**
     * Satu penugasan yang sedang berjalan untuk `lok1`, supaya lokasi
     * benar-benar ikut dihitung (lokasi non-aktif sengaja tidak dihitung).
     */
    protected function penugasanBerjalan(): array
    {
        $mulai  = Carbon::now('Asia/Makassar')->subHours(2)->toDateTimeString();
        $selesai = Carbon::now('Asia/Makassar')->addHours(6)->toDateTimeString();

        return [
            'pen1' => [
                'id_lokasi'     => 'lok1',
                'id_user'       => 'u1',
                'waktu_mulai'   => $mulai,
                'waktu_selesai' => $selesai,
            ],
        ];
    }

    /** Bangun satu record survei pada tanggal tertentu. */
    protected function record(string $tanggal, array $isi, string $jam = '8'): array
    {
        return [
            'lok1' => [
                $tanggal => [
                    $jam => [
                        'pen1' => $isi,
                    ],
                ],
            ],
        ];
    }

    protected function requestDashboard()
    {
        return $this->withSession(['role' => 'admin', 'login_status' => true])
                     ->get(route('admin.live'));
    }

    /**
     * @test
     *
     * Kasus inti: data motor ADA, tapi node master-nya tidak ada.
     *
     * Sebelum perbaikan, `motor` dibuang diam-diam: tidak ada angkanya,
     * tidak ada kolomnya, tidak ada peringatannya.
     */
    public function data_motor_tanpa_master_tetap_dihitung_dan_ditandai()
    {
        $this->tarif = [
            'tarif_mobil' => ['nama' => 'Mobil', 'kunci' => 'mobil', 'harga' => 5000],
        ];

        $this->survei = $this->record(
            Carbon::now('Asia/Makassar')->toDateString(),
            ['motor' => 7, 'mobil' => 3, 'total_survei' => 2, 'user_id' => 'u1']
        );

        $response = $this->requestDashboard();

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard_live');

        // Volume motor tidak hilang.
        $response->assertViewHas('initialGlobal', function ($v) {
            return $v['motor'] === 7 && $v['mobil'] === 3;
        });

        // Terdaftar sebagai tak terpetakan, lengkap dengan volumenya.
        $response->assertViewHas('unmappedKeys', ['motor' => 7]);

        // Punya label, tapi tidak dikarang jadi kendaraan sungguhan.
        $response->assertViewHas('objekNames', function ($v) {
            return ($v['motor'] ?? '') === 'Motor (belum terpetakan)';
        });

        // Harga 0 -> pendapatan tidak pernah dikarang dari data tanpa tarif.
        $response->assertViewHas('objekPrices', function ($v) {
            return $v['motor'] === 0 && $v['mobil'] === 5000;
        });

        // Total dan pecahan per lokasi ikut memuatnya.
        $response->assertViewHas('totalGlobal', 10);
        $response->assertViewHas('initialLokasi', function ($v) {
            return $v['lok1'] === 10;
        });

        // Peringatan terlihat di layar, bukan kode mati.
        $response->assertSee('tidak punya objek di menu Master Tarif', false);
        $response->assertSee('Motor (belum terpetakan)', false);
    }

    /**
     * @test
     *
     * Kontrol: master normal. Perbaikan ini harus sepenuhnya tidak
     * terlihat, supaya tidak mengubah halaman yang sudah benar.
     */
    public function master_lengkap_tidak_berubah_sama_sekali()
    {
        $this->tarif = [
            'tarif_motor' => ['nama' => 'Motor', 'kunci' => 'motor', 'harga' => 2000],
            'tarif_mobil' => ['nama' => 'Mobil', 'kunci' => 'mobil', 'harga' => 5000],
        ];

        $this->survei = $this->record(
            Carbon::now('Asia/Makassar')->toDateString(),
            ['motor' => 7, 'mobil' => 3, 'total_survei' => 2, 'user_id' => 'u1']
        );

        $response = $this->requestDashboard();

        $response->assertStatus(200);

        $response->assertViewHas('unmappedKeys', []);
        $response->assertViewHas('initialGlobal', ['motor' => 7, 'mobil' => 3]);
        $response->assertViewHas('totalGlobal', 10);
        $response->assertViewHas('objekPrices', function ($v) {
            return $v['motor'] === 2000;
        });

        // Tidak ada tanda peringatan sama sekali.
        $response->assertDontSee('belum terpetakan', false);
    }

    /**
     * @test
     *
     * Anti hitung ganda. Satu objek bisa mewakili beberapa jenis
     * kendaraan (`kunci` = "motor,mobil"). Kalau kunci gabungan itu
     * salah dikira sebagai "tak terpetakan", motor dan mobil akan dihitung
     * dua kali: sekali sendiri, sekali lagi di dalam objek gabungan.
     */
    public function kunci_gabungan_tidak_dihitung_ganda()
    {
        $this->tarif = [
            'gabungan' => ['nama' => 'Bus, Pick-up', 'kunci' => 'motor,mobil', 'harga' => 9000],
        ];

        $this->survei = $this->record(
            Carbon::now('Asia/Makassar')->toDateString(),
            ['motor' => 7, 'mobil' => 3]
        );

        $response = $this->requestDashboard();

        $response->assertStatus(200);

        // Tidak ada yang tak terpetakan: keduanya sudah dimiliki.
        $response->assertViewHas('unmappedKeys', []);

        // Terhitung sekali lewat kunci gabungan.
        $response->assertViewHas('initialGlobal', ['motor,mobil' => 10]);
        $response->assertViewHas('totalGlobal', 10);

        // Tidak muncul sebagai kunci terpisah di rendered HTML.
        $response->assertDontSee('id="count-motor"', false);
        $response->assertDontSee('id="count-mobil"', false);
    }

    /**
     * @test
     *
     * Live Dashboard hanya menampilkan HARI INI. Data kemarin tidak boleh
     * ikut, termasuk untuk menentukan kartu kuning. Kalau ini bocor,
     * dashboard pagi-pagi akan menampilkan angka kemarin seolah-olah
     * data hari ini.
     */
    public function data_kemarin_tidak_bocor_ke_dashboard_hari_ini()
    {
        $kemarin = Carbon::now('Asia/Makassar')->subDay()->toDateString();

        $this->tarif = [
            'tarif_mobil' => ['nama' => 'Mobil', 'kunci' => 'mobil', 'harga' => 5000],
        ];

        // Motor hanya ada di Kemarin.
        $this->survei = $this->record($kemarin, ['motor' => 99, 'mobil' => 1]);

        $response = $this->requestDashboard();

        $response->assertStatus(200);

        // Tidak ada kartu kuning, karena hari ini memang tidak ada
        // data yang bermasalah.
        $response->assertViewHas('unmappedKeys', []);
        $response->assertViewHas('totalGlobal', 0);
        $response->assertViewHas('initialLokasi', ['lok1' => 0]);
        $response->assertDontSee('belum terpetakan', false);
    }
}
