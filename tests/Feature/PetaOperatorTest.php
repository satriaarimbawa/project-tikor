<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use App\Support\GeofenceLive;

/**
 * Tes penjaga untuk halaman peta operator.
 *
 * Dua hal yang dijaga di sini:
 *
 * 1. Peta hanya bisa dibuka IT Support. Ini yang membuat koordinat GPS
 *    operator tidak bocor ke akun biasa.
 * 2. Controller tidak pernah menulis ke Firebase. Peta ini alat
 *    monitoring, bukan alat kendali. Kalau suatu saat ada yang menambah
 *    tombol yang menulis, tes di bagian bawah akan gagal.
 *
 * Semua test memakai mock Database, jadi tidak ada yang menyentuh
 * Firebase asli sama sekali.
 */
class PetaOperatorTest extends TestCase
{
    protected $mockDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase = Mockery::mock(Database::class);
        $this->app->instance(Database::class, $this->mockDatabase);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Pasang tiga node Firebase dengan isi yang diberikan.
     */
    private function isiFirebase($live = null, $users = null, $lokasi = null)
    {
        $refLive   = Mockery::mock(Reference::class);
        $refUsers  = Mockery::mock(Reference::class);
        $refLokasi = Mockery::mock(Reference::class);

        $this->mockDatabase->shouldReceive('getReference')->with(GeofenceLive::NODE)->andReturn($refLive);
        $this->mockDatabase->shouldReceive('getReference')->with('users')->andReturn($refUsers);
        $this->mockDatabase->shouldReceive('getReference')->with('lokasi')->andReturn($refLokasi);

        $refLive->shouldReceive('getValue')->andReturn($live ?? []);
        $refUsers->shouldReceive('getValue')->andReturn($users ?? []);
        $refLokasi->shouldReceive('getValue')->andReturn($lokasi ?? []);

        return [$refLive, $refUsers, $refLokasi];
    }

    private function sesiItSupport(): self
    {
        return $this->withSession([
            'login_status'  => true,
            'role'          => 'it_support',
            'username'      => 'IT Support',
            'is_it_support' => true,
        ]);
    }

    // ---------------------------------------------------------------
    // Akses
    // ---------------------------------------------------------------

    public function test_it_support_bisa_membuka_halaman_peta()
    {
        $this->isiFirebase();

        $response = $this->sesiItSupport()->get('/peta-operator');

        $response->assertStatus(200);
        $response->assertSee('Peta Posisi Operator');
        $response->assertSee('Operator');
    }

    public function test_admin_biasa_ditolak_dari_halaman_peta()
    {
        $response = $this->withSession([
            'login_status'  => true,
            'role'          => 'admin',
            'username'      => 'Admin Biasa',
            'is_it_support' => false,
        ])->get('/peta-operator');

        $response->assertRedirect('/dashboard-admin');
        $response->assertSessionHas('error');
    }

    public function test_tamu_tanpa_login_diarahkan_ke_halaman_login()
    {
        $response = $this->get('/peta-operator');

        $response->assertRedirect('/login-admin');
    }

    public function test_tamu_tidak_bisa_memanggil_route_json_peta()
    {
        $response = $this->get('/api/peta-operator/posisi');

        $response->assertRedirect('/login-admin');
    }

    // ---------------------------------------------------------------
    // Isi data
    // ---------------------------------------------------------------

    public function test_route_json_menampilkan_denyut_operator()
    {
        $sekarang = time();

        $this->isiFirebase(
            [
                'op1' => [
                    'lat'            => -8.55,
                    'lng'            => 115.42,
                    'akurasi'        => 12.5,
                    'jarak'          => 40.0,
                    'radius'         => 500.0,
                    'sumber_radius'  => 'global',
                    'id_lokasi'      => 'lok1',
                    'nama_lokasi'    => 'Poso Daya',
                    'username'       => 'Budi',
                    'status'         => GeofenceLive::STATUS_DALAM,
                    'ts'             => $sekarang - 30,
                ],
            ],
            [
                'op1' => ['username' => 'Budi', 'role_user' => 'operator', 'is_online' => true],
            ],
            [
                'lok1' => [
                    'nama_lokasi' => 'Poso Daya',
                    'latitude'    => -8.55,
                    'longitude'   => 115.42,
                    'radius'      => 500,
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        $op = $response->json('operator.0');

        $this->assertSame('Budi', $op['username']);
        $this->assertSame('op1', $op['uid']);
        $this->assertEqualsWithDelta(-8.55, $op['lat'], 0.00001);
        $this->assertEqualsWithDelta(115.42, $op['lng'], 0.00001);
        $this->assertSame(GeofenceLive::STATUS_DALAM, $op['status']);
        $this->assertFalse($op['basi']);
        $this->assertEqualsWithDelta(12.5, $op['akurasi'], 0.00001);
        $this->assertSame('Poso Daya', $op['nama_lokasi']);

        // Titik pos uji ikut dikirim supaya peta punya acuan.
        $this->assertCount(1, $response->json('lokasi'));
        $this->assertSame('Poso Daya', $response->json('lokasi.0.nama'));
    }

    public function test_operator_online_tanpa_denyut_tetap_ditampilkan()
    {
        // Kasus yang paling dicari IT Support saat debug: denyutnya
        // ditolak server, jadi tidak ada koordinat sama sekali.
        $this->isiFirebase(
            [],
            [
                'op9' => ['username' => 'Sari', 'role_user' => 'operator', 'is_online' => true],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $op = $response->json('operator.0');

        $this->assertSame('Sari', $op['username']);
        $this->assertNull($op['lat']);
        $this->assertNull($op['lng']);
        $this->assertSame(GeofenceLive::STATUS_TANPA_LOKASI, $op['status']);
        $this->assertTrue($op['is_online']);
    }

    public function test_koordinat_nol_dianggap_tanpa_posisi()
    {
        // 0,0 adalah titik di teluk Guinea, bukan posisi operator di Bali.
        $this->isiFirebase(
            [
                'op1' => [
                    'lat'  => 0,
                    'lng'  => 0,
                    'ts'   => time(),
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertNull($response->json('operator.0.lat'));
        $this->assertNull($response->json('operator.0.lng'));
        $this->assertSame(GeofenceLive::STATUS_TANPA_LOKASI, $response->json('operator.0.status'));
    }

    public function test_koordinat_di_luar_rentang_world_dibuang()
    {
        $this->isiFirebase(
            [
                'op1' => [
                    'lat' => 991.5,
                    'lng' => 115.42,
                    'ts'  => time(),
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertNull($response->json('operator.0.lat'));
    }

    public function test_denyut_lama_ditandai_sebagai_basi()
    {
        $this->isiFirebase(
            [
                'op1' => [
                    'lat'    => -8.55,
                    'lng'    => 115.42,
                    'status' => GeofenceLive::STATUS_DALAM,
                    'ts'     => time() - (GeofenceLive::UMUR_MAKS + 600),
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $op = $response->json('operator.0');

        $this->assertTrue($op['basi']);
        // Status denyut lama tidak boleh diomosikan sebagai status sekarang.
        $this->assertGreaterThan(GeofenceLive::UMUR_MAKS, $op['umur']);
    }

    public function test_denyut_baru_tidak_ditandai_basi()
    {
        $this->isiFirebase(
            [
                'op1' => [
                    'lat' => -8.55,
                    'lng' => 115.42,
                    'ts'  => time() - 60,
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertFalse($response->json('operator.0.basi'));
    }

    public function test_lokasi_tanpa_field_pisah_memakai_string_koordinat()
    {
        // Node lokasi lama menyimpan satu string "lat,lng".
        $this->isiFirebase(
            [],
            [],
            [
                'lok1' => [
                    'nama_lokasi' => 'Poso Lama',
                    'koordinat'   => '-8.60, 115.50',
                ],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertCount(1, $response->json('lokasi'));
        $this->assertEqualsWithDelta(-8.60, $response->json('lokasi.0.lat'), 0.00001);
        $this->assertEqualsWithDelta(115.50, $response->json('lokasi.0.lng'), 0.00001);
    }

    public function test_lokasi_tanpa_koordinat_dilewati()
    {
        $this->isiFirebase(
            [],
            [],
            [
                'lok1' => ['nama_lokasi' => 'Tanpa Titik'],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertCount(0, $response->json('lokasi'));
    }

    public function test_hanya_akun_role_operator_yang_diambil_dari_users()
    {
        // Hanya akun ber-role operator yang diambil dari node users.
        $this->isiFirebase(
            [],
            [
                'a1' => ['username' => 'Admin Kantor', 'role_user' => 'admin'],
                'o1' => ['username' => 'Operator Desa', 'role_user' => 'operator'],
            ]
        );

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $this->assertCount(1, $response->json('operator'));
        $this->assertSame('o1', $response->json('operator.0.uid'));
    }

    // ---------------------------------------------------------------
    // Penjaga: peta tidak boleh menulis apa pun
    // ---------------------------------------------------------------

    public function test_controller_tidak_pernah_menulis_ke_firebase()
    {
        $refLive   = Mockery::mock(Reference::class);
        $refUsers  = Mockery::mock(Reference::class);
        $refLokasi = Mockery::mock(Reference::class);

        foreach ([$refLive, $refUsers, $refLokasi] as $ref) {
            $ref->shouldReceive('getValue')->andReturn([]);
            $ref->shouldNotReceive('set');
            $ref->shouldNotReceive('update');
            $ref->shouldNotReceive('remove');
            $ref->shouldNotReceive('push');
            $ref->shouldNotReceive('setWithPriority');
            $ref->shouldNotReceive('transaction');
        }

        $this->mockDatabase->shouldReceive('getReference')->with(GeofenceLive::NODE)->andReturn($refLive);
        $this->mockDatabase->shouldReceive('getReference')->with('users')->andReturn($refUsers);
        $this->mockDatabase->shouldReceive('getReference')->with('lokasi')->andReturn($refLokasi);

        $this->sesiItSupport()->get('/peta-operator')->assertStatus(200);
        $this->sesiItSupport()->getJson('/api/peta-operator/posisi')->assertStatus(200);

        $this->assertTrue(true, 'Peta selesai dibaca tanpa satu pun operasi tulis.');
    }

    // ---------------------------------------------------------------
    // Ketahanan
    // ---------------------------------------------------------------

    public function test_firebase_gagal_dibaca_halaman_tetap_buka()
    {
        $ref = Mockery::mock(Reference::class);
        $ref->shouldReceive('getValue')->andThrow(new \RuntimeException('koneksi gagal'));

        $this->mockDatabase->shouldReceive('getReference')->andReturn($ref);

        $response = $this->sesiItSupport()->get('/peta-operator');

        $response->assertStatus(200);
        $response->assertSee('Firebase tidak terbaca');
    }

    public function test_firebase_gagal_dibaca_route_json_tidak_melempar_500()
    {
        $ref = Mockery::mock(Reference::class);
        $ref->shouldReceive('getValue')->andThrow(new \RuntimeException('koneksi gagal'));

        $this->mockDatabase->shouldReceive('getReference')->andReturn($ref);

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $response->assertStatus(200);
        $response->assertJsonPath('ok', false);
        $this->assertCount(0, $response->json('operator'));
    }

    public function test_node_kosong_tidak_menghasilkan_error()
    {
        $this->isiFirebase(null, null, null);

        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
        $this->assertCount(0, $response->json('operator'));
        $this->assertCount(0, $response->json('lokasi'));
    }

    // ---------------------------------------------------------------
    // Penjaga: penanda denyut baru harus tetap terkirim ke browser
    // ---------------------------------------------------------------

    public function test_halaman_peta_mengirim_penanda_denyut_baru_ke_browser()
    {
        // Berdenyutnya pin ditentukan di sisi browser, dari kelas CSS
        // pin-denyut yang dipasang petaOperator.js ketika umur denyut
        // tiba-tiba lebih kecil. Kalau CSS ini hilang, halaman tetap
        // buka dan tes lain tetap lulus, tapi operator tidak pernah
        // terlihat mengirim posisi. Karena itu dijaga di sini.
        $this->isiFirebase();

        $response = $this->sesiItSupport()->get('/peta-operator');

        $response->assertStatus(200);
        $response->assertSee('pin-denyut', false);
        $response->assertSee('denyut-pin', false);
        $response->assertSee('petaOperator.js', false);
    }

    public function test_petunjuk_tertulis_menjelaskan_arti_pin_berdenyut()
    {
        $this->isiFirebase();

        $this->sesiItSupport()->get('/peta-operator')
            ->assertSee('Pin yang berdenyut');
    }
}