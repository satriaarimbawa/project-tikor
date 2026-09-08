<?php

namespace App\Models;

use Kreait\Laravel\Firebase\Facades\Firebase;

class ObjekTarif
{
    protected static $path = 'objek_tarif';

    public static function database()
    {
        return Firebase::database();
    }

    public static function create(array $data)
    {
        $newKey = self::database()->getReference(self::$path)->push()->getKey();
        self::database()->getReference(self::$path . '/' . $newKey)->set($data);
        return $newKey;
    }

    public static function all()
    {
        return self::database()->getReference(self::$path)->getValue();
    }

    public static function find($id)
    {
        return self::database()->getReference(self::$path . '/' . $id)->getValue();
    }

    public static function update($id, array $data)
    {
        self::database()->getReference(self::$path . '/' . $id)->update($data);
    }

    public static function delete($id)
    {
        self::database()->getReference(self::$path . '/' . $id)->remove();
    }
}
