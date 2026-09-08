<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OperatorFeatureTest extends TestCase
{
    use WithoutMiddleware;

    protected $database;
    protected $reference;
    protected $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = Mockery::mock(Database::class);
        $this->reference = Mockery::mock(Reference::class);
        $this->query = Mockery::mock(Query::class);
        
        $this->app->instance(Database::class, $this->database);
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function ia_menampilkan_dashboard_operator_dengan_benar()
    {
        $uid = 'uid_123';
        $idLokasi = 'lokasi_1';
        $today = \Carbon\Carbon::now('Asia/Makassar')->toDateString();
        $hour = \Carbon\Carbon::now('Asia/Makassar')->format('H');
        $idPenugasan = 'tugas_abc_123';
        session(['user_id' => $uid, 'id_lokasi_aktif' => $idLokasi]);

        $this->database->shouldReceive('getReference')->with('lokasi')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->byDefault()->andReturn([$idLokasi => ['nama_lokasi' => 'Terminal']]);

        // Mock Survei Harian Bertingkat: {id_lokasi}/{date}/{hour}/{id_penugasan}
        $this->database->shouldReceive('getReference')->with("survei_harian/{$idLokasi}/{$today}")->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->byDefault()->andReturn([
            $hour => [
                $idPenugasan => [
                    'user_id' => $uid,
                    'motor' => 5,
                    'total_survei' => 5
                ]
            ]
        ]);

        $this->database->shouldReceive('getReference')->with('penugasan')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('id_user')->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($uid)->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('getValue')->byDefault()->andReturn([]);

        $response = $this->get('/dashboard-operator');

        $response->assertStatus(200);
        $response->assertViewHas('totalSemua', 5);
    }

    #[Test]
    public function ia_dapat_menyimpan_jumlah_survei_kendaraan()
    {
        $uid = 'uid_123';
        $idLokasi = 'lokasi_1';
        $idPenugasan = 'tugas_abc_123';
        $today = \Carbon\Carbon::now('Asia/Makassar')->toDateString();
        $hour = \Carbon\Carbon::now('Asia/Makassar')->format('H');
        session(['user_id' => $uid, 'id_lokasi_aktif' => $idLokasi]);

        // Path Baru: survei_harian/{id_lokasi}/{date}/{hour}/{id_penugasan}
        $refPath = "survei_harian/{$idLokasi}/{$today}/{$hour}/{$idPenugasan}";
        
        $this->database->shouldReceive('getReference')->with($refPath)->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->once()->andReturn([
            'id_lokasi' => $idLokasi,
            'user_id' => $uid,
            'motor' => 10,
            'total_survei' => 10
        ]);
        
        $this->reference->shouldReceive('set')->once();

        $response = $this->post('/simpan-hitung-kendaraan', [
            'jenis_kendaraan' => 'motor',
            'id_penugasan' => $idPenugasan
        ]);

        $response->assertJson(['success' => true]);
    }

#[Test]
public function ia_otomatis_logout_dan_set_inaktif_saat_keluar_radius()
{
    $uid = 'uid_123';
    $idLokasi = 'lokasi_1';
    session([
        'user_id' => $uid, 
        'username' => 'mang satria', 
        'id_lokasi_aktif' => $idLokasi,
        'login_status' => true
    ]);

    $this->database->shouldReceive('getReference')->with('lokasi/'.$idLokasi)->byDefault()->andReturn($this->reference);
    $this->reference->shouldReceive('getValue')->byDefault()->andReturn([
        'latitude' => -8.0,
        'longitude' => 115.0,
        'radius' => 100,
        'nama_lokasi' => 'Test Lokasi'
    ]);

    $this->database->shouldReceive('getReference')->with('penugasan')->byDefault()->andReturn($this->reference);
    $this->reference->shouldReceive('getValue')->byDefault()->andReturn([
        'tugas_1' => [
            'id_user' => $uid, 
            'id_lokasi' => $idLokasi, 
            'status' => 'aktif',
            'waktu_mulai' => \Carbon\Carbon::now('Asia/Makassar')->subHour()->toDateTimeString(),
            'waktu_selesai' => \Carbon\Carbon::now('Asia/Makassar')->addHour()->toDateTimeString()
        ]
    ]);

    // Verifikasi Update Status
    $this->database->shouldReceive('getReference')->with('penugasan/tugas_1/status')->andReturn($this->reference);
    $this->reference->shouldReceive('set')->with('inaktif')->once();

    // Verifikasi Notifikasi
    $this->database->shouldReceive('getReference')->with('notifikasi')->andReturn($this->reference);
    $this->reference->shouldReceive('push')->once()->andReturn($this->reference);

    // Akses route pengecekan radius dengan posisi jauh
    $response = $this->post('/check-location-radius', [
        'latitude' => -9.0, 
        'longitude' => 116.0
    ]);

    $response->assertJson(['status' => 'logout']);
}    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
