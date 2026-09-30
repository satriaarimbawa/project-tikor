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

/**
 * Menjamin hash password tidak pernah Stored di node `users`.
 *
 * Node `users` dibaca langsung oleh browser (live dashboard, halaman survei),
 * sedangkan hash password hanya boleh ada di `users_secret` yang tidak diberi
 * aturan `.read` apa pun.
 */
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

        $this->app->instance(Database::class, $this->database);

        Firebase::shouldReceive('database')->andReturn($this->database);
    }

    #[Test]
    public function it_hashes_password_when_registering_a_new_user()
    {
        $newUid = 'new_uid_123';
        $password = 'Secret123!';

        $usersRef  = Mockery::mock(Reference::class);
        $userRef   = Mockery::mock(Reference::class);
        $secretRef = Mockery::mock(Reference::class);

        // 1. Generate key baru di node users.
        $this->database->shouldReceive('getReference')->with('users')->andReturn($usersRef);
        $usersRef->shouldReceive('push')->once()->andReturn($usersRef);
        $usersRef->shouldReceive('getKey')->once()->andReturn($newUid);
        // Controller juga membaca users untuk cek username/email duplikat.
        $usersRef->shouldReceive('getValue')->andReturn([]);

        // 2. Tulis profil ke users, TANPA kolom password.
        $this->database->shouldReceive('getReference')->with('users/' . $newUid)->once()->andReturn($userRef);
        $userRef->shouldReceive('set')->once()->with(Mockery::on(function ($data) {
            return is_array($data)
                && ($data['username'] ?? null) === 'testuser'
                && !array_key_exists('password', $data);
        }));

        // 3. Tulis hash ke users_secret, bukan plaintext.
        $this->database->shouldReceive('getReference')->with('users_secret/' . $newUid)->once()->andReturn($secretRef);
        $secretRef->shouldReceive('set')->once()->with(Mockery::on(function ($data) use ($password) {
            return is_array($data)
                && array_key_exists('password', $data)
                && $data['password'] !== $password
                && Hash::check($password, $data['password']);
        }));

        $response = $this->post('/user/simpan', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role_user' => 'admin',
            'password' => $password
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
        $uid = 'uid_admin';

        $this->mockLoginUser($username, $uid);

        // Hash diambil dari users_secret, bukan dari users.
        $secretRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')
            ->with('users_secret/' . $uid)->once()->andReturn($secretRef);
        $secretRef->shouldReceive('getValue')->once()->andReturn(['password' => $hashedPassword]);

        $userRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('users/' . $uid)->once()->andReturn($userRef);
        $userRef->shouldReceive('update')->once();

        $logRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('activity_logs')->andReturn($logRef);
        $logRef->shouldReceive('push')->once();

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
        $uid = 'uid_admin';

        $this->mockLoginUser($username, $uid);

        $secretRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')
            ->with('users_secret/' . $uid)->once()->andReturn($secretRef);
        $secretRef->shouldReceive('getValue')->once()->andReturn(['password' => $hashedPassword]);

        $response = $this->from('/login')->post('/cek_login', [
            'username' => $username,
            'password' => $wrongPassword
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Username atau password salah!');
    }

    /**
     * Login masih bisa jalan saat hash belum sempat dipindahkan ke
     * users_secret, selama hash lama masih ada di node users.
     */
    #[Test]
    public function it_falls_back_to_legacy_hash_location_during_migration()
    {
        $username = 'admin_user';
        $password = 'mypassword';
        $hashedPassword = Hash::make($password);
        $uid = 'uid_admin';

        $this->mockLoginUser($username, $uid);

        // users_secret belum ada isi.
        $secretRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')
            ->with('users_secret/' . $uid)->once()->andReturn($secretRef);
        $secretRef->shouldReceive('getValue')->once()->andReturn(null);

        // Hash lama masih di users/{uid}/password.
        $legacyRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')
            ->with('users/' . $uid)->once()->andReturn($legacyRef);
        $legacyRef->shouldReceive('getValue')->once()->andReturn(['password' => $hashedPassword]);

        $userRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('users/' . $uid)->once()->andReturn($userRef);
        $userRef->shouldReceive('update')->once();

        $logRef = Mockery::mock(Reference::class);
        $this->database->shouldReceive('getReference')->with('activity_logs')->andReturn($logRef);
        $logRef->shouldReceive('push')->once();

        $response = $this->post('/cek_login', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertRedirect('dashboard-admin');
        $this->assertTrue(session('login_status'));
    }

    /**
     * Jaring pengaman untuk rules: kalau rules berubah lagi, test ini gagal
     * lebih dulu sebelum data bocor ke publik.
     */
    #[Test]
    public function it_keeps_users_secret_closed_in_database_rules()
    {
        $path = base_path('database.rules.json');

        $this->assertFileExists($path);

        $rules = json_decode(file_get_contents($path), true);
        $this->assertIsArray($rules, 'database.rules.json harus berupa JSON valid.');
        $this->assertArrayHasKey('rules', $rules);

        $r = $rules['rules'];

        $this->assertFalse($r['.read'], 'Root .read harus false.');
        $this->assertFalse($r['.write'], 'Root .write harus false.');

        // Penting: `users_secret` ditulis eksplisit `.read: false` (bukan hanya
        // mengandalkan root), supaya tetap tertutup walau aturan root diubah
        // nanti. Root .read false + blok ini = tidak ada jalur baca publik.
        $this->assertArrayHasKey('users_secret', $r, 'Node users_secret harus punya blok rules sendiri.');
        $this->assertFalse($r['users_secret']['.read'], 'users_secret tidak boleh punya .read true.');
        $this->assertFalse($r['users_secret']['.write'], 'users_secret hanya boleh ditulis server.');

        $this->assertTrue($r['users']['.read'], 'users tetap dibaca dashboard.');
        $this->assertTrue($r['survei_harian']['.read'], 'survei_harian tetap dibaca browser.');
        $this->assertTrue($r['activity_logs']['.read'], 'activity_logs tetap dibaca browser.');
        $this->assertTrue($r['lokasi']['.read'], 'lokasi tetap dibaca browser.');
        $this->assertTrue($r['penugasan']['.read'], 'penugasan tetap dibaca browser.');

        $this->assertFalse($r['settings']['.read'], 'settings memuat bot token, harus tertutup.');
    }

    protected function mockLoginUser(string $username, string $uid): void
    {
        $this->database->shouldReceive('getReference')->with('users')->andReturn($this->reference);
        $this->reference->shouldReceive('orderByChild')->with('username')->andReturn($this->query);
        $this->query->shouldReceive('equalTo')->with($username)->andReturn($this->query);

        $this->query->shouldReceive('getValue')->once()->andReturn([
            $uid => [
                'username' => $username,
                'role_user' => 'admin',
                'is_online' => false,
                'last_seen' => 0,
            ]
        ]);

        // Setting sesi tidak wajib; kalau tidak dimock, exception ditangkap
        // controller dan memakai nilai default.
        $this->database->shouldReceive('getReference')
            ->with('settings/session_security')->andReturn(Mockery::mock(Reference::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
