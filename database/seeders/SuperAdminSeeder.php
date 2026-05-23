<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds to create a Super Admin in Firebase.
     */
    public function run(): void
    {
        $database = Firebase::database();
        $usersRef = $database->getReference('users');

        // 1. Cek apakah admin sudah ada agar tidak ganda saat di-run ulang
        $existingUsers = $usersRef->getValue() ?? [];
        foreach ($existingUsers as $user) {
            if (isset($user['email']) && $user['email'] === 'admin.super@dishub.go.id') {
                $this->command->warn('⚠️ Akun Super Admin sudah ada. Proses dibatalkan untuk mencegah duplikasi.');
                return;
            }
        }

        // 2. Siapkan data admin baru
        $adminData = [
            'username' => 'Super Administrator',
            'email' => 'admin.super@dishub.go.id',
            'password' => Hash::make('Dishub#2026!'), // Password terenkripsi kuat
            'role_user' => 'admin',
            'is_online' => false,
            'status_istirahat' => false,
            'last_seen' => Carbon::now()->timestamp,
            'created_at' => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s'),
        ];

        // 3. Masukkan ke Firebase
        try {
            $usersRef->push($adminData);

            $this->command->info('✅ Akun Super Admin berhasil dibuat di Firebase (Production Ready)!');
            $this->command->line('-----------------------------------------');
            $this->command->line('Email    : admin.super@dishub.go.id');
            $this->command->line('Password : Dishub#2026!');
            $this->command->line('-----------------------------------------');
            $this->command->error('PENTING: Segera login dan ubah password ini di menu profil demi keamanan!');
        } catch (\Exception $e) {
            $this->command->error('❌ Gagal membuat admin: ' . $e->getMessage());
        }
    }
}
