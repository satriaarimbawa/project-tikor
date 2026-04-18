<?php

namespace App\Models;

use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseUser
{
    protected static $path = 'users'; // Ini 'folder' di Firebase

    public static function database()
    {
        return Firebase::database();
    }

    // Fungsi untuk Simpan Data (Create)
    public static function create(array $data)
    {
        $newPostKey = self::database()->getReference(self::$path)->push()->getKey();
        self::database()->getReference(self::$path . '/' . $newPostKey)->set($data);
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

    // Fungsi untuk Update Data
    public static function update($id, array $data)
    {
        self::database()->getReference(self::$path . '/' . $id)->update($data);
        return true;
    }

    // Fungsi untuk Hapus Data
    public static function delete($id)
    {
        self::database()->getReference(self::$path . '/' . $id)->remove();
        return true;
    }
}