<?php

namespace Tests\Feature;

use App\Services\PenugasanStatusService;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression test untuk bug "angka Dashboard Admin jadi dua kali lipat".
 *
 * Gejalanya di produksi: Live Dashboard 689 unit, Dashboard Admin 1378
 * unit (tepat 2x), padahal keduanya membaca node Firebase yang sama.
 *
 * Akar masalahnya BUKAN di PHP. `AdminController` sudah benar: satu
 * record survei dihitung satu kali. Yang mengulang adalah JavaScript
 * realtime di `admin/dashboardadmin.blade.php`:
 *
 *     const dates = [todayStr, new Date().toLocaleDateString('en-CA')];
 *
 * Second|date itu adalah tanggal lokal BROWSER, dipakai supaya angka
 * tidak terlihat kosong kalau browser buka di zona waktu lain. Tapi
 * saat browser sudah WITA (kasus paling umum di lapangan), kedua
 * tanggalnya sama persis. Array biasa tidak menghapus duplikat,
 * sehingga loop di bawahnya menjumlahkan data tanggal yang sama dua
 * kali.
 *
 * Yang diuji di sini:
 *   1. render PHP -> tiap unit dihitung satu kali, kunci majemuk
 *      (`sepedamotor,motor`) tetap satu kartu
 *   2. sumber JS  -> daftar tanggal WAJIB di-dedupe, dan tidak boleh
 *      memakai array tanggal mentah yang mengulang
 */
class DashboardAdminRealtimeTest extends TestCase
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
                } elseif ($path === 'notifikasi') {
                    $nilai = [];
                } else {
                    $nilai = null;
                }

                $ref->shouldReceive('getValue')->andReturn($nilai);

                return $ref;
            });
        });

        // Cleanup penugasan kedaluwarsa tidak boleh menyentuh Firebase
        // sungguhan di dalam test.
        $this->mock(PenugasanStatusService::class, function (MockInterface $mock) {
            $mock->shouldReceive('nonaktifkanKedaluwarsa')->andReturnNull();
        });
    }

    /** Master produksi, apa adanya (tidak diubah). */
    protected function masterProduksi(): array
    {
        return [
            'tarif_bus'   => ['nama' => 'Bus',            'harga' => 7000],
            'tarif_mobil' => ['nama' => 'Mobil Penumpang', 'harga' => 3000],
            'tarif_motor' => ['nama' => 'Sepeda Motor',   'harga' => 2000],
            'tarif_truk'  => ['nama' => 'Truk',           'harga' => 7000],
        ];
    }

    /** Bangun data `survei_harian` untuk HARI INI saja. */
    protected function hariIni(array $isi): array
    {
        return [
            'lok1' => [
                Carbon::now('Asia/Makassar')->toDateString() => [
                    '8' => ['pen1' => $isi],
                ],
            ],
        ];
    }

    protected function requestDashboard()
    {
        return $this->withSession(['role' => 'admin', 'login_status' => true])
                     ->get('/dashboard-admin');
    }

    #[Test]
    public function render_php_menghitung_tiap_unit_satu_kali(): void
    {
        $this->tarif = $this->masterProduksi();
        $this->survei = $this->hariIni(['sepedamotor' => 480, 'total_survei' => 2]);

        $response = $this->requestDashboard();

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboardadmin');

        // Kunci data `sepedamotor` dipetakan ke objek `sepedamotor,motor`
        // dan dihitung SATU KALI, tidak 480x2.
        $response->assertViewHas('stats', function ($v) {
            return $v['sepedamotor,motor'] === 480;
        });

        // 480 x Rp 2.000 = Rp 960.000, bukan Rp 1.920.000.
        $response->assertViewHas('totalPendapatan', 960000);

        // Total unit dihitung dari `$stats`, jadi ikut satu kali.
        $this->assertSame(480, array_sum(
            $response->viewData('stats')
        ));
    }

    #[Test]
    public function sub_kunci_dan_kunci_induk_tidak_dihitung_ganda(): void
    {
        $this->tarif = $this->masterProduksi();

        // Dua mode penugasan lama masih hidup di data produksi:
        // `sepedamotor` (nama resmi) dan `motor` (nama pendek).
        $this->survei = $this->hariIni(['sepedamotor' => 7, 'motor' => 3]);

        $response = $this->requestDashboard();

        $response->assertStatus(200);

        // Keduanya milik objek yang sama, jadi dijumlahkan: 7 + 3 = 10.
        $response->assertViewHas('stats', function ($v) {
            return $v['sepedamotor,motor'] === 10;
        });

        // Tidak ada kunci anak yang bocor jadi kartu sendiri.
        $this->assertArrayNotHasKey('sepedamotor', $response->viewData('stats'));
        $this->assertArrayNotHasKey('motor', $response->viewData('stats'));
    }

    #[Test]
    public function realtime_js_mendedupe_daftar_tanggal(): void
    {
        $kode = $this->bladeDashboardAdmin();

        // Sumber perbandingan: Live Dashboard sudah benar sejak awal
        // karena pakai `Array.from(new Set([...]))`. Dashboard Admin
        // harus memakai pola yang sama.
        $this->assertMatchesRegularExpression(
            '/const\s+dates\s*=\s*Array\.from\(new Set\(\[/',
            $kode,
            'Daftar tanggal di dashboardadmin.blade.php harus di-dedupe lewat Array.from(new Set([...])). '
            . 'Tanpa itu, tanggal server dan tanggal browser yang sama dijumlahkan dua kali.'
        );

        // Penjaga tambahan: pola lama yang menyebabkan bug ini tidak
        // boleh muncul lagi dalam bentuk apa pun.
        $this->assertDoesNotMatchRegularExpression(
            '/const\s+dates\s*=\s*\[\s*\w+\s*,/',
            $kode,
            'Pola `const dates = [a, b]` menghitung data tanggal yang sama dua kali.'
        );
    }

    #[Test]
    public function semua_halaman_realtime_mendedupe_tanggal(): void
    {
        // Live Dashboard dan Dashboard Admin harus konsisten: kalau satu
        // dedupe dan yang lain tidak, angkanya akan berbeda padahal
        // sumber datanya sama persis.
        foreach ([
            'admin/dashboard_live.blade.php',
            'admin/dashboardadmin.blade.php',
        ] as $view) {
            $kode = $this->isiBlade($view);

            $this->assertDoesNotMatchRegularExpression(
                '/=\s*\[\s*\w+(Str|)\s*,\s*\w+\s*\]/',
                $kode,
                "Blade {$view} punya daftar tanggal tanpa dedupe."
            );
        }
    }

    /**
     * Isi file blade di folder resources/views.
     *
     * Namanya bukan `blade()` karena method itu sudah dipakai framework
     * untuk merender view.
     */
    protected function isiBlade(string $path): string
    {
        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources'
              . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR
              . str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->assertFileExists($file);

        return (string) file_get_contents($file);
    }

    protected function bladeDashboardAdmin(): string
    {
        return $this->isiBlade('admin/dashboardadmin.blade.php');
    }
}
