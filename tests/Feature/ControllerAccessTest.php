<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Session;
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
    public function it_can_access_tikor_dashboard()
    {
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession(['role' => 'admin', 'login_status' => true])
                         ->get('/dashboard-tikor');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_access_operator_dashboard()
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

        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_access_admin_dashboard()
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
    public function it_can_access_assignment_dashboard()
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
    public function it_redirects_unauthorized_users()
    {
        $response = $this->get('/dashboard-admin');
        $response->assertStatus(302);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
