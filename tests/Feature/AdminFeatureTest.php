<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AdminFeatureTest extends TestCase
{
    use WithoutMiddleware;

    protected $database;
    protected $reference;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = Mockery::mock(Database::class);
        $this->reference = Mockery::mock(Reference::class);
        
        $this->app->instance(Database::class, $this->database);
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function ia_menampilkan_dashboard_admin_dengan_statistik_dinamis()
    {
        $now = \Carbon\Carbon::now('Asia/Makassar');
        $today = $now->toDateString();
        $hour = $now->format('H');
        
        // Mock Objek Tarif
        $refTarif = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('objek_tarif')->andReturn($refTarif);
        $refTarif->shouldReceive('getValue')->andReturn([
            'obj1' => ['nama' => 'Motor', 'harga' => 2000],
            'obj2' => ['nama' => 'Bus', 'harga' => 10000]
        ]);

        // Mock Survei Harian Bertingkat
        $refSurvei = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('survei_harian')->andReturn($refSurvei);
        $refSurvei->shouldReceive('getValue')->andReturn([
            'lokasi_1' => [
                $today => [
                    $hour => [
                        'tugas_1' => [
                            'motor' => 10,
                            'total_survei' => 10
                        ]
                    ]
                ]
            ]
        ]);

        // Mock Notifikasi
        $refNotif = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('notifikasi')->andReturn($refNotif);
        $refNotif->shouldReceive('getValue')->andReturn([]);

        // Mock Lokasi
        $refLokasi = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('lokasi')->andReturn($refLokasi);
        $refLokasi->shouldReceive('getValue')->andReturn([
            'lokasi_1' => ['nama_lokasi' => 'Terminal']
        ]);

        $response = $this->get('/dashboard-admin');

        $response->assertStatus(200);
        $response->assertSee('20.000'); // Verifikasi pendapatan terhitung benar
    }

    #[Test]
    public function ia_dapat_menyimpan_objek_tarif_baru()
    {
        $newKey = 'tarif_123';
        $this->database->shouldReceive('getReference')->with('objek_tarif')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('push')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('getKey')->once()->andReturn($newKey);
        
        $this->database->shouldReceive('getReference')->with('objek_tarif/' . $newKey)->once()->andReturn($this->reference);
        $this->reference->shouldReceive('set')->once()->with(Mockery::on(function($data) {
            return $data['nama'] === 'Kapal Pesiar';
        }));

        $response = $this->post('/Objek_Tarif/store', [
            'nama' => 'Kapal Pesiar',
            'harga' => 50000,
            'status' => 'Aktif'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Data berhasil disimpan ke Firebase!');
    }

    #[Test]
    public function ia_dapat_mengambil_daftar_notifikasi()
    {
        $this->database->shouldReceive('getReference')->with('notifikasi')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([
            'notif1' => ['judul' => 'Test', 'pesan' => 'Pesan Test']
        ]);

        $response = $this->get('/api/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure(['notif1']);
    }

    #[Test]
    public function ia_dapat_mereset_status_penugasan_menjadi_aktif()
    {
        $idTugas = 'tugas_123';
        $this->database->shouldReceive('getReference')->with("penugasan/{$idTugas}/status")->once()->andReturn($this->reference);
        $this->reference->shouldReceive('set')->with('aktif')->once();

        $response = $this->post("/dashboard-penugasan/reset/{$idTugas}");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Status penugasan berhasil diaktifkan kembali!');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
