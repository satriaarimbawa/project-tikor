<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class LoginControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock data for Admin
        $this->adminData = [
            'uid_admin' => [
                'username' => 'admin',
                'password' => Hash::make('Admin123!'),
                'role_user' => 'admin'
            ]
        ];

        // Mock data for Operator
        $this->operatorData = [
            'uid_op' => [
                'username' => 'op',
                'password' => Hash::make('Operator123!'),
                'role_user' => 'operator'
            ]
        ];
    }

    /** @test */
    public function admin_can_use_remember_me()
    {
        $this->mock(Database::class, function (MockInterface $mock) {
            $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
            $referenceMock->shouldReceive('orderByChild->equalTo->getValue')->andReturn($this->adminData);
            $referenceMock->shouldReceive('update')->andReturnTrue();
            $mock->shouldReceive('getReference')->andReturn($referenceMock);
        });

        $response = $this->post('/cek_login', [
            'username' => 'admin',
            'password' => 'Admin123!',
            'remember' => 'on'
        ]);

        $response->assertCookie('remember_user_id', 'uid_admin');
        $response->assertRedirect('/dashboard-admin');
    }

    /** @test */
    public function operator_cannot_use_remember_me()
    {
        $this->mock(Database::class, function (MockInterface $mock) {
            $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
            $referenceMock->shouldReceive('orderByChild->equalTo->getValue')->andReturn($this->operatorData);
            $referenceMock->shouldReceive('update')->andReturnTrue();
            $mock->shouldReceive('getReference')->andReturn($referenceMock);
        });

        $response = $this->post('/cek_login', [
            'username' => 'op',
            'password' => 'Operator123!',
            'remember' => 'on',
            'latitude' => '-8.5353', // Mock GPS data required for operator login
            'longitude' => '115.4042'
        ]);

        $response->assertCookieMissing('remember_user_id');
        $response->assertRedirect('/dashboard-operator-penugasan');
    }
}
