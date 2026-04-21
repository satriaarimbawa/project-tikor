<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
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
    public function ia_mengizinkan_login_jika_berada_dalam_radius_dan_jadwal()
    {
        $now = Carbon::parse('2026-04-18 10:00:00', 'Asia/Makassar');
        Carbon::setTestNow($now);

        $username = 'operator_test';
        $password = 'password123';
        $hashed = Hash::make($password);
        $uid = 'uid_123';
        $idLokasi = 'lokasi_123';

        $this->database->shouldReceive('getReference')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->andReturn($this->query);
        
        $this->query->shouldReceive('getValue')->once()->andReturn([
            $uid => ['username' => $username, 'password' => $hashed, 'role_user' => 'operator']
        ]);

        $this->reference->shouldReceive('getValue')->andReturnValues([
            [
                ['id_user' => $uid, 'id_lokasi' => $idLokasi, 'waktu_mulai' => '2026-04-18 08:00:00', 'waktu_selesai' => '2026-04-18 12:00:00', 'status' => 'aktif']
            ],
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

        $response->assertRedirect('dashboard-operator-penugasan');
        
        Carbon::setTestNow();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
