<?php

namespace App\Models;

use Kreait\Laravel\Firebase\Facades\Firebase;

/**
 * Akses data user di Firebase Realtime Database.
 *
 * PEMISAHAN RAHASIA
 * -----------------
 * Node `users` dibaca langsung oleh browser (live dashboard, halaman survei),
 * sedangkan hash password tidak boleh ada di sana. Karena itu field
 * `password` dipisah ke node `users_secret`, yang TIDAK diberi aturan `.read`
 * apa pun sehingga hanya bisa dibaca lewat Admin SDK dari sisi server.
 *
 * Bentuknya:
 *   users        -> { "<uid>": { username, email, role_user, ... } }
 *   users_secret -> { "<uid>": { password: "<hash>" } }
 *
 * Semua pemanggil create/update/delete tidak perlu diubah: pemisahan terjadi
 * di dalam kelas ini.
 */
class FirebaseUser
{
    protected static $path = 'users'; // Ini 'folder' di Firebase, dibaca browser
    protected static $secretPath = 'users_secret'; // Hanya server, tanpa aturan .read

    public static function database()
    {
        return Firebase::database();
    }

    /**
     * Lokasi node rahasia. Dipakai juga oleh command migrasi
     * `users:pindahkan-rahasia` supaya hanya ada satu sumber kebenaran.
     */
    public static function secretPath($id = null)
    {
        return $id === null ? self::$secretPath : self::$secretPath . '/' . $id;
    }

    /**
     * Ambil field `password` dari data dan sisanya dikembalikan untuk `users`.
     *
     * @return array{0: array, 1: array} [dataUmum, dataRahasia]
     */
    protected static function pisahkan(array $data)
    {
        $rahasia = [];

        if (array_key_exists('password', $data)) {
            $rahasia['password'] = $data['password'];
            unset($data['password']);
        }

        return [$data, $rahasia];
    }

    // Fungsi untuk Simpan Data (Create)
    public static function create(array $data)
    {
        [$dataUmum, $dataRahasia] = self::pisahkan($data);

        $newPostKey = self::database()->getReference(self::$path)->push()->getKey();

        if ($dataUmum) {
            self::database()->getReference(self::$path . '/' . $newPostKey)->set($dataUmum);
        }

        if ($dataRahasia) {
            self::database()->getReference(self::secretPath($newPostKey))->set($dataRahasia);
        }

        return $newPostKey;
    }

    // Fungsi untuk Ambil Semua Data (Read All)
    public static function all()
    {
        return self::database()->getReference(self::$path)->getValue();
    }

    // Fungsi untuk Ambil Satu Data berdasarkan ID
    public static function find($id)
    {
        return self::database()->getReference(self::$path . '/' . $id)->getValue();
    }

    /**
     * Ambil hash password satu user.
     *
     * Ada fallback ke lokasi lama (`users/{id}/password`) supaya urutan deploy
     * tidak penting: kode boleh naik sebelum migrasi dijalankan maupun
     * sebaliknya.
     * Fallback ini otomatis mati begitu command `users:pindahkan-rahasia`
     * selesai memindahkan seluruh hash.
     */
    public static function passwordHash($id)
    {
        if ($id === null || $id === '') {
            return null;
        }

        $rahasia = self::database()->getReference(self::secretPath($id))->getValue();
        if (!empty($rahasia['password'])) {
            return $rahasia['password'];
        }

        $lama = self::database()->getReference(self::$path . '/' . $id)->getValue();
        if (!empty($lama['password'])) {
            return $lama['password'];
        }

        return null;
    }

    // Fungsi untuk Update Data
    public static function update($id, array $data)
    {
        [$dataUmum, $dataRahasia] = self::pisahkan($data);

        if ($dataUmum) {
            self::database()->getReference(self::$path . '/' . $id)->update($dataUmum);
        }

        if ($dataRahasia) {
            self::database()->getReference(self::secretPath($id))->update($dataRahasia);
        }

        return true;
    }

    // Fungsi untuk Hapus Data
    public static function delete($id)
    {
        self::database()->getReference(self::$path . '/' . $id)->remove();
        self::database()->getReference(self::secretPath($id))->remove();
        return true;
    }
}
