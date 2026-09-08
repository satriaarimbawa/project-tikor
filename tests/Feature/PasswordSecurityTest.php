<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
use Illuminate\Support\Facades\Hash;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PasswordSecurityTest extends TestCase
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
        
        // Mocking the database for both Firebase methods
        $this->app->instance(Database::class, $this->database);
        
        // Mock the Firebase facade
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function it_hashes_password_when_registering_a_new_user()
    {
        $newUid = 'new_uid_123';
        
        // Mocking Firebase database push and set behavior
        $this->database->shouldReceive('getReference')->with('users')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('push')->once()->andReturn($this->reference);
        $this->reference->shouldReceive('getKey')->once()->andReturn($newUid);
        
        // Mocking the second call to getReference with the full path
        $this->database->shouldReceive('getReference')->with('users/' . $newUid)->once()->andReturn($this->reference);
        
        // We capture the data being set to verify hashing
        $this->reference->shouldReceive('set')->once()->with(Mockery::on(function ($data) {
            return $data['username'] === 'testuser' && 
                   $data['password'] !== 'secret123' && // Password must not be plain text
                   Hash::check('secret123', $data['password']); // Hash must match
        }));

        $response = $this->post('/user/simpan', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'admin',
            'password' => 'secret123'
        ]);

        $response->assertRedirect('/daftar-user');
        $response->assertSessionHas('success', 'User berhasil ditambahkan!');
    }

    #[Test]
    public function it_allows_login_with_correct_hashed_password()
    {
        $username = 'admin_user';
        $password = 'mypassword';
        $hashedPassword = Hash::make($password);

        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('username')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($username)->andReturn($this->query);
        
        // Mock user record from Firebase
        $this->query->shouldReceive('getValue')->once()->andReturn([
            'uid_admin' => [
                'username' => $username,
                'password' => $hashedPassword, // Stored as hash
                'role_user' => 'admin'
            ]
        ]);

        $response = $this->post('/cek_login', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertRedirect('dashboard-admin');
        $this->assertTrue(session('login_status'));
    }

    #[Test]
    public function it_denies_login_with_wrong_password()
    {
        $username = 'admin_user';
        $correctPassword = 'correct_password';
        $wrongPassword = 'wrong_password';
        $hashedPassword = Hash::make($correctPassword);

        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('username')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($username)->andReturn($this->query);
        
        $this->query->shouldReceive('getValue')->once()->andReturn([
            'uid_admin' => [
                'username' => $username,
                'password' => $hashedPassword,
                'role_user' => 'admin'
            ]
        ]);

        $response = $this->from('/login')->post('/cek_login', [
            'username' => $username,
            'password' => $wrongPassword
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Password salah!');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
