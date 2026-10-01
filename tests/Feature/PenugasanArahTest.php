<?php

namespace Tests\Feature;

use App\Http\Controllers\PenugasanController;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test untuk field `arah` pada penugasan.
 *
 * Latar belakang: kolom "Arah" di PDF Laporan Uji Petik
 * (`operator/report_pdf.blade.php`) sudah lama ada, tapi tidak pernah bisa
 * terisi. `OperatorController` mengirim
 * `$tugasAktif['arah'] ?? '....................'`, sedangkan node `penugasan`
 * tidak pernah punya field `arah` karena Form Penugasan tidak menawarkannya.
 * Akibatnya PDF selalu mencetak titik-titik, padahal operator sudah tahu
 * sedang menghitung arah mana.
 *
 * Yang dijaga di sini:
 *   1. dropdown arah muncul di Form Penugasan (create dan edit)
 *   2. preset arah jadi satu sumber kebenaran (kontrol + validasi)
 *   3. arah di luar preset ditolak
 *   4. edit penugasan lama yang belum punya `arah` tidak merusak data
 *   5. garis titik placeholder tidak lagi dipakai
 */
class PenugasanArahTest extends TestCase
{
    /** Data yang dikembalikan controller `create()` / `edit()`. */
    protected array $lokasi  = [];
    protected array $users   = [];
    protected array $objek   = [];

    /** Data penugasan untuk uji `edit` / `update`. */
    protected ?array $penugasanTersimpan = null;

    /** Payload terakhir yang dikirim ke Firebase pada `update()`. */
    protected ?array $payloadUpdate = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lokasi = ['lok1' => ['nama_lokasi' => 'Lokasi Satu']];
        $this->users  = ['u1' => ['username' => 'Operator Satu', 'role_user' => 'operator']];
        $this->objek  = ['tarif_motor' => ['nama' => 'Sepeda Motor', 'status' => 'Aktif']];
    }

    /**
     * Pasang mock Database.
     *
     * Semua pembacaan dikembalikan dari properti kelas, dan `update()`
     * pada `penugasan/{id}` ditangkap ke `$payloadUpdate` supaya isi yang
     * benar-benar akan ditulis ke Firebase bisa diperiksa.
     */
    protected function pasangDatabase(): void
    {
        $this->mock(Database::class, function (MockInterface $mock) {
            $mock->shouldReceive('getReference')->andReturnUsing(function ($path) {
                $ref = \Mockery::mock('Kreait\Firebase\Database\Reference');

                $nilai = match (true) {
                    $path === 'users'                        => $this->users,
                    $path === 'lokasi'                       => $this->lokasi,
                    $path === 'objek_tarif'                  => $this->objek,
                    str_starts_with((string) $path, 'penugasan/') => $this->penugasanTersimpan,
                    default                                 => null,
                };

                $ref->shouldReceive('getValue')->andReturn($nilai);

                $ref->shouldReceive('update')->andReturnUsing(function ($payload) {
                    $this->payloadUpdate = $payload;

                    return $ref;
                });

                // Sengaja TIDAK ada mock untuk push() / remove(): kalau ada
                // kode yang menulis ke Firebase di luar jalur yang diuji,
                // test ini harus gagal, bukan diam-diam lewat.
                return $ref;
            });
        });
    }

    /** Sesi minimal agar middleware `admin` mengizinkan akses. */
    protected function sesiAdmin()
    {
        return $this->withSession(['login_status' => true, 'role' => 'admin']);
    }

    /** Isi form penugasan yang selalu valid, bisa ditimpa per test. */
    protected function form(array $ubah = []): array
    {
        return array_merge([
            'id_user'        => 'u1',
            'id_lokasi'      => 'lok1',
            'waktu_mulai'    => '2026-10-01 08:00',
            'waktu_selesai'  => '2026-10-01 16:00',
            'objek_terpilih' => 'sepedamotor',
        ], $ubah);
    }

    #[Test]
    public function preset_arah_hanya_ada_di_satu_tempat(): void
    {
        // Ini sumber kebenaran untuk dropdown, validasi, dan PDF sekaligus.
        $this->assertSame(
            ['Timur', 'Barat', 'Utara', 'Selatan'],
            PenugasanController::ARAH_PRESET
        );

        $this->assertNotEmpty(PenugasanController::ARAH_KOSONG);
        $this->assertNotContains(
            PenugasanController::ARAH_KOSONG,
            PenugasanController::ARAH_PRESET,
            'Nilai kosong tidak boleh ikut jadi pilihan arah.'
        );
    }

    #[Test]
    public function form_penugasan_menampilkan_dropdown_arah(): void
    {
        $this->pasangDatabase();

        $response = $this->sesiAdmin()->get('/dashboard-penugasan/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.penugasan.form_penugasan');

        // Field harus benar-benar terkirim ke server.
        $response->assertSee('name="arah"', false);

        foreach (PenugasanController::ARAH_PRESET as $arah) {
            $response->assertSee('value="' . $arah . '"', false);
        }
    }

    #[Test]
    public function edit_penugasan_menandai_arah_yang_tersimpan(): void
    {
        $this->pasangDatabase();

        $this->penugasanTersimpan = [
            'id_user'      => 'u1',
            'id_lokasi'    => 'lok1',
            'objek_survei' => 'sepedamotor',
            'arah'         => 'Barat',
            'waktu_mulai'  => '2026-10-01 08:00:00',
            'waktu_selesai'=> '2026-10-01 16:00:00',
        ];

        $response = $this->sesiAdmin()->get('/dashboard-penugasan/edit/p1');

        $response->assertStatus(200);
        $response->assertViewIs('admin.penugasan.form_penugasan');

        // Arah lama harus terpilih, bukan hilang lalu harus dipilih ulang.
        $this->assertStringContainsString(
            'value="Barat" selected',
            $this->rBlade($response->getContent()),
            'Arah yang tersimpan harus terpilih di dropdown saat edit.'
        );
    }

    #[Test]
    public function arah_di_luar_preset_ditolak(): void
    {
        $this->pasangDatabase();

        $response = $this->sesiAdmin()->post(
            '/dashboard-penugasan/store',
            $this->form(['arah' => 'Kutub'])
        );

        $response->assertSessionHasErrors('arah');

        // Gagal validasi = tidak boleh ada tulisan ke Firebase.
        $this->assertNull($this->payloadUpdate);
    }

    #[Test]
    public function arah_dari_preset_diterima(): void
    {
        $this->pasangDatabase();

        $response = $this->sesiAdmin()->post(
            '/dashboard-penugasan/store',
            $this->form(['arah' => 'Timur'])
        );

        // Field arah sendiri tidak boleh jadi sumber error.
        // (`surat_spt` tetaprequired, tapi itu urusan berkas SPT.)
        $response->assertSessionDoesntHaveErrors(['arah']);
    }

    #[Test]
    public function update_dengan_arah_baru_menyimpan_nilai_baru(): void
    {
        $this->pasangDatabase();

        $this->penugasanTersimpan = $this->penugasanLama();

        $this->sesiAdmin()->post(
            '/dashboard-penugasan/update/p1',
            $this->form(['arah' => 'Utara'])
        );

        $this->assertIsArray($this->payloadUpdate);
        $this->assertSame('Utara', $this->payloadUpdate['arah'] ?? null);
    }

    #[Test]
    public function update_tanpa_arah_tidak_menimpa_arah_yang_sudah_ada(): void
    {
        $this->pasangDatabase();

        // Penugasan versi lama: sudah punya arah.
        $this->penugasanTersimpan = $this->penugasanLama(['arah' => 'Selatan']);

        $this->sesiAdmin()->post('/dashboard-penugasan/update/p1', $this->form());

        $this->assertIsArray($this->payloadUpdate);
        $this->assertSame(
            'Selatan',
            $this->payloadUpdate['arah'] ?? null,
            'Edit tanpa memilih arah tidak boleh menghapus arah yang sudah tersimpan.'
        );
    }

    #[Test]
    public function update_penugasan_lama_tanpa_arah_tetap_aman(): void
    {
        $this->pasangDatabase();

        // Penugasan yang dibuat sebelum field `arah` ada sama sekali.
        $this->penugasanTersimpan = $this->penugasanLama();

        $this->sesiAdmin()->post('/dashboard-penugasan/update/p1', $this->form());

        $this->assertIsArray($this->payloadUpdate);
        $this->assertSame(
            PenugasanController::ARAH_KOSONG,
            $this->payloadUpdate['arah'] ?? null,
            'Penugasan lama harus dapat nilai kosong yang rapi, bukan error.'
        );
    }

    #[Test]
    public function pdf_tidak_lagi_mencetak_garis_titik(): void
    {
        $kode = file_get_contents(
            dirname(__DIR__, 2) . '/app/Http/Controllers/OperatorController.php'
        );

        $this->assertIsString($kode);
        $this->assertStringNotContainsString(
            '....................',
            $kode,
            'Placeholder garis titik untuk kolom Arah sudah diganti nilai kosong yang rapi.'
        );
    }

    #[Test]
    public function pdf_tetap_menampilkan_baris_arah(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/operator/report_pdf.blade.php'
        );

        $this->assertIsString($view);
        $this->assertMatchesRegularExpression(
            '/\{\{\s*\$arah\s*\}\}/',
            $view,
            'Kolom Arah di PDF harus tetap ada.'
        );
    }

    /**
     * Data penugasan lama, tanpa field `arah` kecuali diminta.
     */
    protected function penugasanLama(array $tambahan = []): array
    {
        return array_merge([
            'id_user'       => 'u1',
            'id_lokasi'     => 'lok1',
            'objek_survei'  => 'sepedamotor',
            'file_spt'      => 'dummy.pdf',
            'keterangan'    => '-',
            'status'        => 'aktif',
            'waktu_mulai'   => '2026-10-01 08:00:00',
            'waktu_selesai' => '2026-10-01 16:00:00',
        ], $tambahan);
    }

    /**
     * Blade menimpa spasi di sekitar atribut yang dirender, jadi pola
     * "value="Barat" selected" tidak selalu-litera.
     */
    protected function rBlade(?string $html): string
    {
        return (string) preg_replace('/\s+/', ' ', (string) $html);
    }
}