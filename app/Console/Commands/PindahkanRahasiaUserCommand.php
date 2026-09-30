<?php

namespace App\Console\Commands;

use App\Models\FirebaseUser;
use Illuminate\Console\Command;

/**
 * Memindahkan hash password dari node `users` ke node `users_secret`.
 *
 * LATAR BELAKANG
 * --------------
 * Node `users` dibaca langsung oleh browser, dan node `settings` yang
 * memuat bot token ikut terbuka karena root `.read` masih `true` (aturan
 * `.read` yang lebih dangkal menimpa yang lebih dalam, jadi blok
 * `settings: { ".read": false }` tidak benar-benar menutup apa pun).
 *
 * Command ini memindahkan `password` ke `users_secret`, yang tidak diberi
 * aturan `.read` apa pun, lalu menghapus kolom `password` dari `users`.
 * Hash yang sudah ada TIDAK diubah: nilainya disalin apa adanya, sehingga
 * tidak ada user yang perlu login ulang atau lupa password.
 *
 * URUTAN PENTING
 * --------------
 * 1. Jalankan `--dry-run` lebih dulu.
 * 2. Jalankan tanpa `--dry-run` (pindah hash, bersihkan `users`).
 * 3. Baru publish rules baru di Firebase Console.
 *
 * Isi hash tidak pernah ditampilkan; command hanya mencetak panjangnya
 * sebagai bukti bahwa nilainya terbaca utuh.
 */
class PindahkanRahasiaUserCommand extends Command
{
    protected $signature = 'users:pindahkan-rahasia
                            {--dry-run : Tampilkan hasil tanpa menulis ke database}';

    protected $description = 'Pindahkan hash password dari users ke users_secret lalu bersihkan kolom password di users';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== PINDAHKAN RAHASIA PASSWORD (users -> users_secret) ===');
        $this->line('Node tujuan: ' . FirebaseUser::secretPath() . '/<uid>/password');
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: tidak ada perubahan yang ditulis ke database.');
        }
        $this->newLine();

        $database = FirebaseUser::database();

        try {
            $users = $database->getReference('users')->getValue() ?? [];
        } catch (\Throwable $e) {
            $this->error('Gagal membaca node users: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!is_array($users) || $users === []) {
            $this->info('Node users kosong. Tidak ada yang perlu dipindahkan.');
            return self::SUCCESS;
        }

        $adaHash    = 0;
        $tanpaHash  = 0;
        $berhasil   = 0;
        $gagal      = 0;

        foreach ($users as $uid => $user) {
            $uid = (string) $uid;
            $hashLama = $user['password'] ?? null;

            if (empty($hashLama)) {
                $tanpaHash++;
                $this->line(sprintf('  - [%s] tidak punya kolom password di users (wajar, sudah pindah).', $uid));
                continue;
            }

            $adaHash++;

            if ($dryRun) {
                $this->line(sprintf(
                    '  - [%s] username=%s hash=%d karakter (akan disalin ke %s)',
                    $uid,
                    $user['username'] ?? '-',
                    strlen((string) $hashLama),
                    FirebaseUser::secretPath($uid)
                ));
                continue;
            }

            try {
                // 1. Salin hash apa adanya ke users_secret (tidak di-hash ulang).
                $database->getReference(FirebaseUser::secretPath($uid))->update([
                    'password' => (string) $hashLama,
                ]);

                // 2. Verifikasi kembali sebelum kolom aslinya dihapus.
                $tersimpan = $database->getReference(FirebaseUser::secretPath($uid))->getValue();
                if (($tersimpan['password'] ?? null) !== (string) $hashLama) {
                    throw new \RuntimeException('hash tidak cocok setelah ditulis');
                }

                // 3. Baru hapus kolom password dari users.
                $database->getReference('users/' . $uid . '/password')->remove();

                $this->line(sprintf(
                    '  - [%s] username=%s hash=%d karakter ->users_secret, kolom users dibersihkan',
                    $uid,
                    $user['username'] ?? '-',
                    strlen((string) $hashLama)
                ));
                $berhasil++;
            } catch (\Throwable $e) {
                $this->error(sprintf('  - [%s] GAGAL: %s', $uid, $e->getMessage()));
                $gagal++;
            }
        }

        $this->newLine();
        $this->line('Ringkasan: ' . count($users) . ' user, '
            . $adaHash . ' punya hash di users, '
            . $tanpaHash . ' sudah tanpa hash, '
            . $berhasil . ' berhasil, '
            . $gagal . ' gagal.');

        if ($dryRun) {
            $this->info('DRY-RUN selesai. Tidak ada yang ditulis.');
            $this->comment('Jalankan tanpa --dry-run bila ingin menerapkan.');
        } else {
            $this->info('Selesai. Jangan publish rules baru sebelum tes login berhasil.');
        }

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
