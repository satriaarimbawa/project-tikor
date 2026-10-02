<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use App\Support\GeofenceLive;

/**
 * Tes end-to-end alur denyut operator sampai ke peta.
 *
 * PetaOperatorTest injecting data denyut secara langsung ke Firebase
 * tiruan. Itu cukup untuk menguji cara peta MEMBACA data, tapi tidak
 * menguji apakah denyutnya benar-benar sampai.
 *
 * File ini menutup celah itu: satu penyimpanan in-memory dipakai
 * bersama oleh dua request nyata.
 *
 *   1. POST /check-location-radius  -> LoginController::checkLocationRadius
 *      -> GeofenceLive::catat() menulis geofence_live/{uid}
 *   2. GET  /api/peta-operator/posisi -> PetaOperatorController membaca
 *      node yang sama persis
 *
 * Kalau sambungan antara denyut dan peta ini putus, test di bawah gagal.
 * Tidak ada Firebase asli yang disentuh: Database di-bind ke tiruan
 * in-memory, jadi test ini aman dijalankan di laptop maupun di server.
 */
class DenyutOperatorTest extends TestCase
{
    /** @var array Firebase tiruan, isi shared antar request. */
    private $store = [];

    /** @var int Penghitung kunci untuk simulasi push(). */
    private $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = $this->isiAwal();
        $this->urut  = 0;

        $db = Mockery::mock(Database::class);
        $db->shouldReceive('getReference')->andReturnUsing(function ($path) {
            return $this->rujuk($path);
        });

        $this->app->instance(Database::class, $db);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ---------------------------------------------------------------
    // Sesi
    // ---------------------------------------------------------------

    private function sesiOperator(array $tambahan = []): self
    {
        return $this->withSession(array_merge([
            'login_status'      => true,
            'role'              => 'operator',
            'user_id'           => 'op1',
            'username'          => 'Budi',
            'id_lokasi_aktif'   => 'lok1',
            'nama_lokasi_aktif' => 'Poso Daya',
        ], $tambahan));
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
    // Firebase tiruan in-memory
    // ---------------------------------------------------------------

    private function isiAwal(): array
    {
        return [
            'users' => [
                'op1' => [
                    'username'         => 'Budi',
                    'role_user'        => 'operator',
                    'is_online'        => true,
                    'status_istirahat' => false,
                    'last_seen'        => 0,
                ],
            ],
            'lokasi' => [
                'lok1' => [
                    'nama_lokasi' => 'Poso Daya',
                    'latitude'    => -8.55,
                    'longitude'   => 115.42,
                    'radius'      => 500,
                ],
            ],
            'settings' => [
                'geofencing' => [
                    'default_radius'  => 500,
                    'operator_radius' => [],
                ],
            ],
            'penugasan' => [],
        ];
    }

    /** Baca nilai whatever di path mana pun. */
    private function ambil(string $path)
    {
        $node = $this->store;

        foreach ($this->segmen($path) as $seg) {
            if (!is_array($node) || !array_key_exists($seg, $node)) {
                return null;
            }
            $node = $node[$seg];
        }

        return $node;
    }

    /** Tulis (timpa) nilai di path mana pun, membuat node antara bila perlu. */
    private function tulis(string $path, $value): void
    {
        $this->store = $this->pasang($this->store, $this->segmen($path), $value);
    }

    /** Update (gabung) nilai di path mana pun. */
    private function perbarui(string $path, $value): void
    {
        $lama = $this->ambil($path);

        $gabung = (is_array($lama) && is_array($value))
            ? array_merge($lama, $value)
            : $value;

        $this->tulis($path, $gabung);
    }

    /** Hapus nilai di path mana pun. */
    private function hapus(string $path): void
    {
        $this->store = $this->buang($this->store, $this->segmen($path));
    }

    /** Push: buat kunci baru lalu set, persis seperti Firebase. */
    private function tambah(string $path, $value): string
    {
        $this->urut++;
        $kunci = 'k' . str_pad((string) $this->urut, 4, '0', STR_PAD_LEFT);

        $this->tulis($path . '/' . $kunci, $value);

        return $kunci;
    }

    private function segmen(string $path): array
    {
        return array_values(array_filter(explode('/', trim($path, '/')), function ($s) {
            return $s !== '';
        }));
    }

    /**
     * Susun nilai ke dalam path. Nilai bisa apa saja, termasuk skalar
     * (misal users/{uid}/last_seen yang isinya angka), jadi return type
     * sengaja tidak dikunci ke array.
     */
    private function pasang(array $node, array $segs, $value)
    {
        if (empty($segs)) {
            return $value;
        }

        $seg = array_shift($segs);

        $anak = (isset($node[$seg]) && is_array($node[$seg])) ? $node[$seg] : [];

        $node[$seg] = $this->pasang($anak, $segs, $value);

        return $node;
    }

    private function buang(array $node, array $segs): array
    {
        if (empty($segs)) {
            return [];
        }

        $seg = array_shift($segs);

        if (!array_key_exists($seg, $node)) {
            return $node;
        }

        if (empty($segs)) {
            unset($node[$seg]);
            return $node;
        }

        if (!is_array($node[$seg])) {
            return $node;
        }

        $node[$seg] = $this->buang($node[$seg], $segs);

        return $node;
    }

    /** Reference tiruan yang terikat ke $this->store. */
    private function rujuk(string $path): Reference
    {
        $ref = Mockery::mock(Reference::class);

        $ref->shouldReceive('getValue')->andReturnUsing(function () use ($path) {
            return $this->ambil($path);
        });

        $ref->shouldReceive('set')->andReturnUsing(function ($value) use ($path) {
            $this->tulis($path, $value);
            return $this->rujuk($path);
        });

        $ref->shouldReceive('update')->andReturnUsing(function ($value) use ($path) {
            $this->perbarui($path, $value);
            return $this->rujuk($path);
        });

        $ref->shouldReceive('remove')->andReturnUsing(function () use ($path) {
            $this->hapus($path);
        });

        $ref->shouldReceive('push')->andReturnUsing(function ($value) use ($path) {
            return new KunciPush($this->tambah($path, $value));
        });

        return $ref;
    }

    // ---------------------------------------------------------------
    // Alur lengkap: denyut -> peta
    // ---------------------------------------------------------------

    public function test_denyut_dalam_radius_tercatat_lalu_muncul_di_peta()
    {
        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.5501,
            'longitude' => 115.4201,
            'accuracy'  => 12.5,
        ])->assertStatus(200)->assertJsonPath('status', 'ok');

        // Harus ada node yang benar-benar ditulis server.
        $denyut = $this->ambil('geofence_live/op1');

        $this->assertIsArray($denyut, 'Denyut tidak pernah ditulis ke node geofence_live.');
        $this->assertEqualsWithDelta(-8.5501, $denyut['lat'], 0.000001);
        $this->assertEqualsWithDelta(115.4201, $denyut['lng'], 0.000001);
        $this->assertEqualsWithDelta(12.5, $denyut['akurasi'], 0.000001);
        $this->assertSame(GeofenceLive::STATUS_DALAM, $denyut['status']);
        $this->assertSame('Budi', $denyut['username']);
        $this->assertSame('Poso Daya', $denyut['nama_lokasi']);

        // Peta membaca denyut yang sama persis, tanpa data tambahan.
        $response = $this->sesiItSupport()->getJson('/api/peta-operator/posisi');

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        $op = $response->json('operator.0');

        $this->assertSame('op1', $op['uid']);
        $this->assertSame('Budi', $op['username']);
        $this->assertEqualsWithDelta($denyut['lat'], $op['lat'], 0.000001);
        $this->assertEqualsWithDelta($denyut['lng'], $op['lng'], 0.000001);
        $this->assertSame(GeofenceLive::STATUS_DALAM, $op['status']);
        $this->assertFalse($op['basi']);
        $this->assertTrue($op['is_online']);

        // Pos uji juga terbaca, jadi peta punya lingkaran radius.
        $this->assertCount(1, $response->json('lokasi'));
    }

    public function test_denyut_luar_radius_tercatat_walau_tidak_ada_penugasan_berjalan()
    {
        // Kasus yang paling sering dicari IT Support: operator terlihat
        // jauh dari lokasi tugas. Catatan posisi harus ditulis SEBELUM
        // server memutuskan apa pun.
        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.60,
            'longitude' => 115.42,
            'accuracy'  => 8,
        ])->assertStatus(200);

        $denyut = $this->ambil('geofence_live/op1');

        $this->assertIsArray($denyut, 'Denyut di luar radius tidak tercatat.');
        $this->assertSame(GeofenceLive::STATUS_LUAR, $denyut['status']);
        $this->assertGreaterThan(500, $denyut['jarak']);
        $this->assertSame(500.0, (float) $denyut['radius']);
        $this->assertSame('lokasi', $denyut['sumber_radius']);

        $op = $this->sesiItSupport()->getJson('/api/peta-operator/posisi')->json('operator.0');

        $this->assertSame(GeofenceLive::STATUS_LUAR, $op['status']);
        $this->assertEqualsWithDelta(-8.60, $op['lat'], 0.000001);
        $this->assertFalse($op['basi']);
    }

    public function test_denyut_saat_istirahat_tercatat()
    {
        $this->store['users']['op1']['status_istirahat'] = true;

        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.55,
            'longitude' => 115.42,
            'accuracy'  => 5,
        ])->assertStatus(200)->assertJsonPath('status', 'ok');

        $denyut = $this->ambil('geofence_live/op1');

        $this->assertSame(GeofenceLive::STATUS_ISTIRAHAT, $denyut['status']);

        $op = $this->sesiItSupport()->getJson('/api/peta-operator/posisi')->json('operator.0');

        $this->assertSame(GeofenceLive::STATUS_ISTIRAHAT, $op['status']);
    }

    public function test_denyut_tanpa_tugas_aktif_tercatat_sebagai_tanpa_lokasi()
    {
        $this->sesiOperator(['id_lokasi_aktif' => null, 'nama_lokasi_aktif' => null])
            ->postJson('/check-location-radius', [
                'latitude'  => -8.55,
                'longitude' => 115.42,
                'accuracy'  => 5,
            ])->assertStatus(200);

        $denyut = $this->ambil('geofence_live/op1');

        $this->assertSame(GeofenceLive::STATUS_TANPA_LOKASI, $denyut['status']);
        $this->assertNull($denyut['jarak']);

        $op = $this->sesiItSupport()->getJson('/api/peta-operator/posisi')->json('operator.0');

        $this->assertSame(GeofenceLive::STATUS_TANPA_LOKASI, $op['status']);
    }

    public function test_heartbeat_tanpa_user_id_tidak_menulis_denyut()
    {
        // Sesi tidak punya user_id: guard 401 harus menahan sebelum
        // apa pun ditulis. Ini yang bikin denyut kosong di peta.
        $this->withSession([
            'login_status' => true,
            'role'         => 'operator',
            'username'     => 'Tanpa ID',
        ])->postJson('/check-location-radius', [
            'latitude'  => -8.55,
            'longitude' => 115.42,
            'accuracy'  => 5,
        ])->assertStatus(401);

        $this->assertNull($this->ambil('geofence_live/op1'));
    }

    public function test_denyut_lama_ditandai_basi_di_peta()
    {
        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.5501,
            'longitude' => 115.4201,
            'accuracy'  => 12.5,
        ])->assertStatus(200);

        // Mundurkan waktu denyut, lalu minta peta lagi.
        $denyut = $this->ambil('geofence_live/op1');
        $denyut['ts'] = $denyut['ts'] - (GeofenceLive::UMUR_MAKS + 600);
        $this->tulis('geofence_live/op1', $denyut);

        $op = $this->sesiItSupport()->getJson('/api/peta-operator/posisi')->json('operator.0');

        $this->assertTrue($op['basi'], 'Denyut lama harus ditandai basi di peta.');
        $this->assertGreaterThan(GeofenceLive::UMUR_MAKS, $op['umur']);
    }

    public function test_pembersihan_menghapus_hanya_denyut_yang_basi()
    {
        // Dua operator: satu denyut baru, satu denyut lama.
        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.5501,
            'longitude' => 115.4201,
            'accuracy'  => 12.5,
        ])->assertStatus(200);

        $lama = [
            'lat'          => -8.60,
            'lng'          => 115.50,
            'status'       => GeofenceLive::STATUS_DALAM,
            'username'     => 'Sari',
            'ts'           => time() - (GeofenceLive::UMUR_MAKS + 600),
        ];
        $this->tulis('geofence_live/op_lama', $lama);

        // Pembersihan hanya jalan bila jeda meta sudah lewat. Buang
        // penanda jadwal supaya kondisi itu terpenuhi.
        $this->hapus('geofence_live_meta/jadwal');

        $this->sesiOperator()->postJson('/check-location-radius', [
            'latitude'  => -8.5502,
            'longitude' => 115.4202,
            'accuracy'  => 12.5,
        ])->assertStatus(200);

        $this->assertNull($this->ambil('geofence_live/op_lama'), 'Denyut basi harus dibuang.');
        $this->assertIsArray($this->ambil('geofence_live/op1'), 'Denyut baru harus tetap ada.');
    }
}

/**
 * Objek yang dikembalikan reference->push(), hanya perlu getKey().
 */
class KunciPush
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}