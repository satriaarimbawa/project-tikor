<?php

namespace App\Support;

use Throwable;

/**
 * Menyimpan satu "kode laporan" singkat untuk setiap exception yang lolos ke
 * handler Laravel. Dipakai halaman error supaya pengguna bisa membaca kode
 * yang bisa dicari di notifikasi Telegram, tanpa membuka stack trace.
 *
 * PENTING: kode harus dibentuk dari exception ASLI, yaitu dari callback
 * report(). Kalau dibentuk dari objek $exception di dalam view error, nilai
 * getFile() sudah ditimpa menjadi Handler.php sehingga SEMUA error 500 akan
 * menghasilkan kode yang sama persis.
 */
class KodeLaporan
{
    private static ?string $kode = null;

    /**
     * Bentuk dan simpan kode laporan. Panggilan pertama dalam satu request
     * yang menang, supaya halaman error dan notifikasi Telegram memakai kode
     * yang sama.
     */
    public static function dari(Throwable $e): string
    {
        if (self::$kode === null) {
            $bahan = get_class($e) . '|' . $e->getFile() . '|' . $e->getLine();
            self::$kode = strtoupper(substr(hash('sha256', $bahan), 0, 8));
        }

        return self::$kode;
    }

    /**
     * Kode yang sudah dibentuk pada request ini, atau null bila report()
     * belum pernah dipanggil.
     */
    public static function terakhir(): ?string
    {
        return self::$kode;
    }
}
