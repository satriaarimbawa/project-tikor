<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Query;
use Mockery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AuthFeatureTest extends TestCase
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
        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function ia_mengizinkan_login_dengan_kredensial_yang_benar()
    {
        $username = 'admin_test';
        $password = 'password123';
        $hashed = Hash::make($password);

        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('username')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($username)->andReturn($this->query);
        $this->query->shouldReceive('getValue')->once()->andReturn([
            'uid_1' => ['username' => $username, 'password' => $hashed, 'role_user' => 'admin']
        ]);

        $response = $this->post('/cek_login', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertRedirect('dashboard-admin');
        $this->assertEquals('admin', session('role'), 'Role di session harus admin');
    }

    #[Test]
    public function ia_mengirim_otp_jika_email_terdaftar()
    {
        Mail::fake();
        $email = 'user@example.com';

        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('email')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($email)->andReturn($this->query);
        $this->query->shouldReceive('getValue')->once()->andReturn(['uid_1' => ['email' => $email]]);

        $this->database->shouldReceive('getReference')->with(Mockery::pattern('/password_resets/'))->andReturn($this->reference);
        $this->reference->shouldReceive('set')->once();

        $response = $this->post('/forgot-password', ['email' => $email]);

        $response->assertRedirect('/verify-otp');
    }

    #[Test]
    public function ia_berhasil_mereset_password()
    {
        $email = 'user@example.com';
        $newPassword = 'newpassword123';
        
        session(['reset_email' => $email, 'otp_verified' => true]);

        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('email')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($email)->andReturn($this->query);
        $this->query->shouldReceive('getValue')->once()->andReturn(['uid_1' => ['role_user' => 'operator']]);

        $this->database->shouldReceive('getReference')->with('users/uid_1/password')->andReturn($this->reference);
        $this->reference->shouldReceive('set')->once();

        $this->database->shouldReceive('getReference')->with(Mockery::pattern('/password_resets/'))->andReturn($this->reference);
        $this->reference->shouldReceive('remove')->once();

        $response = $this->post('/reset-password', [
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertRedirect('/login');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
