<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;

class SettingControllerTest extends TestCase
{
    protected $mockDatabase;
    protected $mockRef;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase = Mockery::mock(Database::class);
        $this->mockRef = Mockery::mock(Reference::class);
        $this->app->instance(Database::class, $this->mockDatabase);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_support_can_access_settings_page()
    {
        $this->mockDatabase->shouldReceive('getReference')->with('settings')->andReturn($this->mockRef);
        $this->mockDatabase->shouldReceive('getReference')->with('users')->andReturn($this->mockRef);
        $this->mockDatabase->shouldReceive('getReference')->with('lokasi')->andReturn($this->mockRef);
        $this->mockRef->shouldReceive('getValue')->andReturn([]);

        $response = $this->withSession([
            'login_status' => true,
            'role' => 'it_support',
            'username' => 'IT Support',
            'is_it_support' => true,
        ])->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Radius Operator');
        $response->assertSee('Bot Telegram');
    }

    public function test_normal_admin_is_rejected_from_settings_page()
    {
        $response = $this->withSession([
            'login_status' => true,
            'role' => 'admin',
            'username' => 'Admin Biasa',
            'is_it_support' => false,
        ])->get('/settings');

        $response->assertRedirect('/dashboard-admin');
        $response->assertSessionHas('error', 'Akses ditolak! Halaman Pengaturan Sistem khusus untuk akun IT Support.');
    }

    public function test_it_support_can_update_radius_settings()
    {
        $this->mockDatabase->shouldReceive('getReference')->with('settings/geofencing')->andReturn($this->mockRef);
        $this->mockDatabase->shouldReceive('getReference')->with('activity_logs')->andReturn($this->mockRef);
        $this->mockRef->shouldReceive('set')->once();
        $this->mockRef->shouldReceive('push')->once();

        $response = $this->withSession([
            'login_status' => true,
            'role' => 'it_support',
            'username' => 'IT Support',
            'is_it_support' => true,
        ])->post('/settings/radius', [
            'default_radius' => 150,
            'cutoff_hour' => 21,
            'heartbeat_interval_seconds' => 60,
            'operator_radius' => [
                'uid_op_1' => 200
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_it_support_can_update_session_security()
    {
        $this->mockDatabase->shouldReceive('getReference')->with('settings/session_security')->andReturn($this->mockRef);
        $this->mockDatabase->shouldReceive('getReference')->with('activity_logs')->andReturn($this->mockRef);
        $this->mockRef->shouldReceive('set')->once();
        $this->mockRef->shouldReceive('push')->once();

        $response = $this->withSession([
            'login_status' => true,
            'role' => 'it_support',
            'username' => 'IT Support',
            'is_it_support' => true,
        ])->post('/settings/session-security', [
            'max_sessions_per_account' => 2,
            'stale_timeout_seconds' => 600,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
