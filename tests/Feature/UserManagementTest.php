<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use Illuminate\Support\Facades\Hash;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UserManagementTest extends TestCase
{
    use WithoutMiddleware;

    protected $database;
    protected $reference;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = Mockery::mock(Database::class);
        $this->reference = Mockery::mock(Reference::class);
        
        $this->app->instance(Database::class, $this->database);
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function ia_dapat_menampilkan_semua_daftar_user()
    {
        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('getValue')->andReturn([
            'uid1' => ['username' => 'user1', 'email' => 'u1@mail.com'],
            'uid2' => ['username' => 'user2', 'email' => 'u2@mail.com']
        ]);

        $response = $this->get('/daftar-user');

        $response->assertStatus(200);
        $response->assertSee('user1');
        $response->assertSee('user2');
    }

    #[Test]
    public function ia_dapat_memperbarui_data_user()
    {
        $uid = 'uid_to_update';
        $this->database->shouldReceive('getReference')->with("users/{$uid}")->andReturn($this->reference);
        $this->reference->shouldReceive('update')->once();

        $response = $this->post("/user/update/{$uid}", [
            'username' => 'updated_name',
            'email' => 'updated@mail.com',
            'role_user' => 'admin'
        ]);

        $response->assertRedirect('/daftar-user');
        $response->assertSessionHas('success', 'User berhasil diperbarui!');
    }

    #[Test]
    public function ia_dapat_menghapus_user()
    {
        $uid = 'uid_to_delete';
        $this->database->shouldReceive('getReference')->with("users/{$uid}")->andReturn($this->reference);
        $this->reference->shouldReceive('remove')->once();

        $response = $this->delete("/user/hapus/{$uid}");

        $response->assertRedirect('/daftar-user');
        $response->assertSessionHas('success', 'User berhasil dihapus!');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
