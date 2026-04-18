<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
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
    }

    #[Test]
    public function it_can_access_operator_index_with_data()
    {
        $userId = 'test_uid';
        $idLokasi = 'test_lokasi';

        $this->database->shouldReceive('getReference')->with('hasil_survei')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('id_user')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($userId)->andReturn($this->query);
        $this->query->shouldReceive('getValue')->andReturn([
            ['id_lokasi' => $idLokasi, 'jenis_kendaraan' => 'motor', 'created_at' => now()->toDateTimeString()],
        ]);

        $this->database->shouldReceive('getReference')->with('lokasi')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([
            $idLokasi => ['nama_lokasi' => 'Terminal Galiran']
        ]);

        $response = $this->withSession([
            'role' => 'operator',
            'login_status' => true,
            'user_id' => $userId,
            'id_lokasi_aktif' => $idLokasi
        ])->get('/dashboard-operator');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_save_survey_count()
    {
        $this->database->shouldReceive('getReference')->with('hasil_survei')->andReturn($this->reference);
        $this->reference->shouldReceive('push')->once()->andReturn($this->reference);

        $response = $this->withSession([
            'role' => 'operator',
            'login_status' => true,
            'user_id' => 'test_uid',
            'id_lokasi_aktif' => 'test_lokasi'
        ])->postJson('/simpan-hitung-kendaraan', [
            'jenis_kendaraan' => 'bus'
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_fails_to_access_survei_without_active_location()
    {
        $response = $this->withSession([
            'role' => 'operator',
            'login_status' => true,
            'user_id' => 'test_uid'
        ])->get('/dashboard-operator-survei');

        $response->assertRedirect('/dashboard-operator-penugasan');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
