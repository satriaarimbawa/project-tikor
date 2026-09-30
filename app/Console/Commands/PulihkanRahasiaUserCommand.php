<?php

namespace App\Console\Commands;

use App\Models\FirebaseUser;
use Illuminate\Console\Command;

/**
 * Memulihkan hash password dari file cadangan hasil `users:backup-rahasia`.
 *
 * KAPAN DIPAKAI
 * -------------
 * Hanya saat perlu membatalkan migrasi, yaitu mengembalikan hash password ke
 * tempatnya semula. Hash disalin apa adanya (tidak di-hash ulang), jadi
 * password asli user langsung berlaku kembali tanpa reset.
 *
 * TUJUHAN
 * -------
 * Default: menulis ke `users_secret/<uid>/password`. Ini jalur yang dipakai
 * kode aplikasi (`FirebaseUser::passwordHash()`), jadi cukup untuk memulihkan
 * login tanpa menyentuh node `users` sama sekali.
 *
 * Opsi `--ke-users` menulis juga ke `users/<uid>/password`, yaitu membatalkan
 * pemisahan sepenuhnya. Hanya perlu kalau `users_secret` ikut hilang atau
 * ditolak aturan `.read` sehingga app tidak bisa membacanya. Opsi ini
 * sengaja harus diketik eksplisit agar tidak ada tulisan tak sengaja ke
 * node `users` yang dibaca publik.
 *
 * Isi hash tidak pernah ditampilkan ke layar.
 */
class PulihkanRahasiaUserCommand extends Command
{
    protected $signature = 'users:pulihkan-rahasia
                            {--from= : Path file cadangan JSON (wajib)}
                            {--dry-run : Tampilkan hasil tanpa menulis ke database}
                            {--ke-users : Tulis juga ke users/<uid>/password (rollback penuh)}';

    protected $description = 'Pulihkan hash password dari file cadangan ke users_secret';

    public function handle(): int
    {
        $this->info('=== PULIHKAN RAHASIA PASSWORD DARI CADANGAN ===');

        $path = trim((string) $this->option('from'));
        if ($path === '') {
            $this->error('Opsi --from wajib diisi, contoh: --from=storage/app/rahasia/backup-users-20260930-120000.json');
            return self::FAILURE;
        }

        $sAbsolute = str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
        $file = $sAbsolute ? $path : base_path($path);

        if (!is_file($file)) {
            $this->error('File cadangan tidak ditemukan: ' . $file);
            return self::FAILURE;
        }

        $raw = (string) @file_get_contents($file);
        $data = json_decode($raw, true);

        if (!is_array($data) || $data === []) {
            $this->error('File cadangan tidak berisi objek JSON yang valid: ' . $file);
            return self::FAILURE;
        }

        $this->line('File cadangan : ' . $file);
        $this->line('Entri terbaca : ' . count($data));
        $this->line('Tujuan        : ' . FirebaseUser::secretPath() . '/<uid>/password');
        if ($this->option('ke-users')) {
            $this->warn('Mode --ke-users: hash juga ditulis ke users/<uid>/password (node yang dibaca publik).');
        }
        if ($this->option('dry-run')) {
            $this->warn('Mode DRY-RUN: tidak ada perubahan yang ditulis ke database.');
        }
        $this->newLine();

        // Validasi bentuk file sebelum menyentuh database sama sekali.
        foreach ($data as $uid => $row) {
            if (!is_array($row) || empty($row['password']) || !is_string($row['password'])) {
                $this->error('Entri tidak valid untuk uid "' . $uid . '": field password kosong atau bukan teks.');
                return self::FAILURE;
            }
        }

        $database = FirebaseUser::database();
        $ditulis = 0;
        $gagal = 0;

        foreach ($data as $uid => $row) {
            $uid = (string) $uid;
            $hash = (string) $row['password'];

            try {
                $tujuan = [FirebaseUser::secretPath($uid)];
                if ($this->option('ke-users')) {
                    $tujuan[] = 'users/' . $uid . '/password';
                }

                if ($this->option('dry-run')) {
                    $this->line(sprintf(
                        '  - [%s] hash=%d karakter -> %s',
                        $uid,
                        strlen($hash),
                        implode(' dan ', $tujuan)
                    ));
                    $ditulis++;
                    continue;
                }

                foreach ($tujuan as $reference) {
                    $database->getReference($reference)->set(['password' => $hash]);

                    $tersimpan = $database->getReference($reference)->getValue();
                    if (($tersimpan['password'] ?? null) !== $hash) {
                        throw new \RuntimeException('hash tidak cocok setelah ditulis ke ' . $reference);
                    }
                }

                $this->line(sprintf(
                    '  - [%s] hash=%d karakter dipulihkan ke %s',
                    $uid,
                    strlen($hash),
                    implode(' dan ', $tujuan)
                ));
                $ditulis++;
            } catch (\Throwable $e) {
                $this->error(sprintf('  - [%s] GAGAL: %s', $uid, $e->getMessage()));
                $gagal++;
            }
        }

        $this->newLine();
        $this->line('Ringkasan: ' . count($data) . ' entri, '
            . $ditulis . ($this->option('dry-run') ? ' akan ditulis' : ' berhasil') . ', '
            . $gagal . ' gagal.');

        if ($this->option('dry-run')) {
            $this->info('DRY-RUN selesai. Tidak ada yang ditulis.');
        } else {
            $this->info('Selesai. Uji login satu user sebelum pemulihan dianggap berhasil.');
            $this->comment('Kalau hash ikut ditulis ke users, rules lama yang menimbun password bisa dibiarkan dulu.');
        }

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
