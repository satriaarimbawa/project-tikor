<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Kreait\Firebase\Contract\Database;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Hash;

class DaftarUserControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mocking Firebase Database
        $this->mock(Database::class, function (MockInterface $mock) {
            $referenceMock = \Mockery::mock('Kreait\Firebase\Database\Reference');
            $referenceMock->shouldReceive('getValue')->andReturn([]);
            $referenceMock->shouldReceive('push')->andReturnTrue();
            $referenceMock->shouldReceive('update')->andReturnTrue();
            
            $mock->shouldReceive('getReference')->andReturn($referenceMock);
        });
    }

    /** @test */
    public function password_must_be_at_least_6_characters()
    {
        $response = $this->post('/daftar-user', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'operator',
            'password' => 'Ab1!', // Only 4 chars
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_contain_numbers()
    {
        $response = $this->post('/daftar-user', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'operator',
            'password' => 'Abcdef!', // No number
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_contain_symbols()
    {
        $response = $this->post('/daftar-user', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'operator',
            'password' => 'Abcdef1', // No symbol
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_contain_uppercase()
    {
        $response = $this->post('/daftar-user', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'operator',
            'password' => 'abcdef1!', // No uppercase
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function valid_password_is_accepted()
    {
        $response = $this->post('/daftar-user', [
            'username' => 'validuser',
            'email' => 'valid@example.com',
            'role_user' => 'operator',
            'password' => 'Valid123!', 
        ]);

        $response->assertRedirect('/daftar-user');
        $response->assertSessionHas('success');
    }
}
