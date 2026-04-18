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
    public function it_displays_dashboard_operator_correctly()
    {
        $uid = 'uid_123';
        $idLokasi = 'lokasi_1';
        session(['user_id' => $uid, 'id_lokasi_aktif' => $idLokasi]);

        $this->database->shouldReceive('getReference')->with('objek_tarif')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->byDefault()->andReturn(['obj1' => ['nama' => 'Motor']]);

        $this->database->shouldReceive('getReference')->with('hasil_survei')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('id_user')->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($uid)->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('getValue')->byDefault()->andReturn([]);

        $this->database->shouldReceive('getReference')->with('lokasi')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->byDefault()->andReturn([$idLokasi => ['nama_lokasi' => 'Terminal']]);

        $this->database->shouldReceive('getReference')->with('penugasan')->byDefault()->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('id_user')->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($uid)->byDefault()->andReturn($this->query);
        $this->query->shouldReceive('getValue')->byDefault()->andReturn([]);

        $response = $this->get('/dashboard-operator');

        $response->assertStatus(200);
        $response->assertViewHas('nama_lokasi', 'Terminal');
    }

    #[Test]
    public function it_can_save_vehicle_survey_count()
    {
        $uid = 'uid_123';
        $idLokasi = 'lokasi_1';
        session(['user_id' => $uid, 'id_lokasi_aktif' => $idLokasi]);

        $this->database->shouldReceive('getReference')->with('hasil_survei')->andReturn($this->reference);
        
        // Simpan hitung memanggil push($data) langsung
        $this->reference->shouldReceive('push')->with(Mockery::on(function($data) {
            return $data['jenis_kendaraan'] === 'Motor' && 
                   $data['id_lokasi'] === 'lokasi_1';
        }))->once()->andReturn($this->reference);

        $response = $this->post('/simpan-hitung-kendaraan', [
            'jenis_kendaraan' => 'Motor'
        ]);

        $response->assertJson(['success' => true]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
