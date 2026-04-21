<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;

class ControllerAccessTest extends TestCase
{
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
    public function ia_dapat_mengakses_dashboard_tikor()
    {
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/dashboard-tikor');

        $response->assertStatus(200, 'Admin harus bisa mengakses dashboard tikor');
    }

    #[Test]
    public function ia_dapat_mengakses_dashboard_operator()
    {
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->andReturn($this->query);
        $this->query->shouldReceive('getValue')->andReturn([]);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession([
            'role' => 'operator', 
            'login_status' => true,
            'user_id' => 'test_uid',
            'id_lokasi_aktif' => 'test_lokasi'
        ])->get('/dashboard-operator');

        $response->assertStatus(200, 'Operator harus bisa mengakses dashboard-nya');
    }

    #[Test]
    public function ia_dapat_mengakses_dashboard_admin()
    {
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession([
            'role' => 'admin', 
            'login_status' => true,
            'username' => 'Test Admin'
        ])->get('/dashboard-admin');

        $response->assertStatus(200);
        $response->assertSee('Pendapatan Harian');
    }

    #[Test]
    public function ia_dapat_mengakses_dashboard_penugasan()
    {
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->andReturn($this->query);
        $this->query->shouldReceive('getValue')->andReturn([]);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/dashboard-penugasan');

        $response->assertStatus(200);
    }

    #[Test]
    public function ia_mengalihkan_pengguna_yang_tidak_sah()
    {
        $response = $this->get('/dashboard-admin');
        $response->assertStatus(302, 'Pengguna tanpa login harus dialihkan');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
