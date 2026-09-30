<?php

namespace App\Console\Commands;

use App\Models\FirebaseUser;
use Illuminate\Console\Command;

/**
 * Membuat salinan cadangan hash password ke file JSON lokal.
 *
 * MUNDURNYA
 * ----------
 * Command ini dipakai SEBELUM `users:pindahkan-rahasia` dijalankan, supaya
 * hash password yang nanti dihapus dari node `users` masih punya salinan.
 * Tanpa cadangan, Once hash hilang dari `users`, satu-satunya cara mengembalikan
 * login semua user adalah meminta mereka reset password satu per satu.
 *
 * BERAPA YANG MASUK FILE
 * ----------------------
 * Hanya `uid` dan `password`. Tidak ada email, username, nomor telepon, lokasi,
 * atau token Telegram. Ini disengaja supaya file cadangan tidak menjadi
 * kebocoran baru: `settings/telegram/bot_token` tidak pernah ikut tersalin.
 *
 * Isi hash tidak pernah ditampilkan ke layar; command hanya mencetak
 * jumlahnya, panjangnya, dan hasil verifikasi.
 *
 * KEAMANAN FILE
 * -------------
 * File ditulis ke `storage/app/rahasia/` yang sudah tercakup `.gitignore`
 * (`storage/app/.gitignore` berisi `*`), jadi tidak bisa ikut ter-commit.
 * Izin file diset 0600 (hanya pemilik yang bisa baca).
 * Setelah ditulis, file dibaca ulang dan dibandingkan hash per hash; kalau
 * satu saja tidak cocok, command berhenti dengan kode gagal.
 *
 * PEMULIHAN
 * ---------
 * Pakai `users:pulihkan-rahasia --from=<file>`.
 */
class BackupRahasiaUserCommand extends Command
{
    protected $signature = 'users:backup-rahasia
                            {--ke=storage/app/rahasia : Folder tujuan file cadangan}';

    protected $description = 'Cadangkan hash password ke file JSON lokal (hanya uid + hash)';

    public function handle(): int
    {
        $this->info('=== BACKUP RAHASIA PASSWORD ===');
        $this->line('Isi file: hanya uid + hash password. Email, username, dan token TIDAK ikut.');
        $this->newLine();

        try {
            $users = FirebaseUser::database()->getReference('users')->getValue() ?? [];
        } catch (\Throwable $e) {
            $this->error('Gagal membaca node users: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!is_array($users) || $users === []) {
            $this->error('Node users kosong. Backup dibatalkan agar file lama tidak tertimpa.');
            return self::FAILURE;
        }

        $data = [];
        $tanpaHash = 0;

        foreach ($users as $uid => $user) {
            $uid = (string) $uid;

            // passwordHash() membaca users_secret dulu, lalu fallback ke users.
            $hash = FirebaseUser::passwordHash($uid);

            if (empty($hash)) {
                $tanpaHash++;
                $this->line(sprintf(
                    '  - [%s] username=%s tidak punya hash di users maupun users_secret',
                    $uid,
                    (is_array($user) ? ($user['username'] ?? '-') : '-')
                ));
                continue;
            }

            $data[$uid] = ['password' => (string) $hash];
        }

        if ($data === []) {
            $this->error('Tidak ada satu pun hash yang bisa dicadangkan. Backup dibatalkan.');
            return self::FAILURE;
        }

        $folder = $this->resolveFolder((string) $this->option('ke'));
        if (!is_dir($folder) && !@mkdir($folder, 0700, true) && !is_dir($folder)) {
            $this->error('Gagal membuat folder: ' . $folder);
            return self::FAILURE;
        }

        $file = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR
            . 'backup-users-' . date('Ymd-His') . '.json';

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || @file_put_contents($file, $json) === false) {
            $this->error('Gagal menulis file: ' . $file);
            return self::FAILURE;
        }
        @chmod($file, 0600);

        // Verifikasi ulang: file yang baru ditulis harus identik dengan yang dicadangkan.
        $cek = json_decode((string) @file_get_contents($file), true);
        if (!is_array($cek) || count($cek) !== count($data)) {
            $this->error('Verifikasi GAGAL: isi file tidak cocok dengan data yang dicadangkan.');
            return self::FAILURE;
        }

        foreach ($data as $uid => $row) {
            if (($cek[$uid]['password'] ?? null) !== $row['password']) {
                $this->error('Verifikasi GAGAL: hash untuk [' . $uid . '] tidak cocok setelah ditulis.');
                return self::FAILURE;
            }
        }

        $panjang = array_values(array_unique(array_map(
            static fn (array $r) => strlen((string) $r['password']),
            $data
        )));

        $this->newLine();
        $this->line('File             : ' . $file);
        $this->line('User dicadangkan : ' . count($data));
        $this->line('Tanpa hash       : ' . $tanpaHash);
        $this->line('Panjang hash     : ' . implode(', ', $panjang) . ' karakter');
        $this->line('Ukuran file      : ' . number_format((int) filesize($file)) . ' byte');
        $this->line('Verifikasi       : OK, ' . count($data) . ' hash identik setelah file ditulis');
        $this->newLine();
        $this->comment('File ini berisi hash password. Jangan di-commit, jangan dikirim lewat chat.');
        $this->comment('Hapus setelah migrasi berhasil dan tes login selesai.');

        return self::SUCCESS;
    }

    /**
     * Path relatif dihitung dari base_path(); path absolut dipakai apa adanya
     * supaya file cadangan bisa dipindah ke luar folder project bila perlu.
     */
    private function resolveFolder(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = 'storage/app/rahasia';
        }

        $sAbsolute = str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;

        return $sAbsolute ? $path : base_path($path);
    }
}
