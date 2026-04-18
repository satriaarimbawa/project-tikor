<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class LoginGeofencingTest extends TestCase
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
    public function it_allows_login_if_within_radius_and_schedule()
    {
        // Set fixed time for consistency
        $now = Carbon::parse('2026-04-18 10:00:00', 'Asia/Makassar');
        Carbon::setTestNow($now);

        $username = 'operator_test';
        $password = 'password123';
        $uid = 'uid_123';
        $idLokasi = 'lokasi_123';

        // Set expectations in order of execution
        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->andReturn($this->query);
        
        // Return values in order: 1. User data, 2. All Assignments, 3. Specific Location data
        $this->query->shouldReceive('getValue')->once()->andReturn([
            $uid => ['username' => $username, 'password' => $password, 'role_user' => 'operator']
        ]);

        $this->reference->shouldReceive('getValue')->andReturnValues([
            // Assignment list
            [
                ['id_user' => $uid, 'id_lokasi' => $idLokasi, 'waktu_mulai' => '2026-04-18 08:00:00', 'waktu_selesai' => '2026-04-18 12:00:00']
            ],
            // Location data
            [
                'latitude' => -8.5353,
                'longitude' => 115.4042,
                'radius' => 100,
                'nama_lokasi' => 'Kantor Bupati'
            ]
        ]);

        $response = $this->from('/login')->post('/cek_login', [
            'username' => $username,
            'password' => $password,
            'latitude' => -8.53531,
            'longitude' => 115.40421
        ]);

        if (session('error')) {
            $this->fail('Login failed with error: ' . session('error'));
        }

        $response->assertRedirect('dashboard-operator-penugasan');
        
        Carbon::setTestNow(); // Reset time
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
