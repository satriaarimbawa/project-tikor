<?php

namespace App\Support;

/**
 * Sumber tunggal untuk "kunci data" tiap objek di master `objek_tarif`.
 *
 * ==========================  MASALAH  =============================
 * Kunci di `survei_harian` berasal dari NAMA objek PADA SAAT penugasan
 * dibuat. Nama itu dibekukan di `penugasan.objek_survei`, lalu
 * OperatorController::simpanHitung() menormalisasinya lagi:
 *
 *     "Mini Bus"  ->  strtolower(str_replace(' ', '', ...))  ->  "minibus"
 *     "Pick-up"   ->  strtolower(str_replace(' ', '', ...))  ->  "pick-up"
 *
 * Fakta historis: data historis memakai taksonomi LAMA
 * ("Motor", "Mini Bus", "Pick-up", "Truk"), sedangkan master `nama`
 * sudah di-rename admin ke taksonomi BARU ("Sepeda Motor",
 * "Mobil Penumpang", "Bus", "Truk").
 *
 * Laporan ikut memakai `nama` LIVE, jadi begitu admin me-rename, kunci
 * laporan ikut berubah dan seluruh data historis objek itu lenyap tanpa
 * error. Itulah akar bug "sebagian data survei tidak muncul".
 *
 * ==========================  SOLUSI  ==============================
 * Pisahkan identitas data dari label tampilan:
 *
 *   `kunci` : identitas permanen. Diisi OTOMATIS oleh kode saat objek
 *             dibuat (ObjekTarifController::store), dan TIDAK PERNAH ikut
 *             berubah saat admin me-rename.
 *   `nama`  : label tampilan saja, bebas diubah admin.
 *
 * Admin tidak pernah mengetik `kunci` — kode yang mengisinya.
 * Objek baru -> kunci terbentuk sendiri, tanpa tindakan manual.
 *
 * Urutan resolution:
 *   1. field `kunci` di database  -> objek baru / node yang sudah dimigrasi
 *   2. PETA_LEGACY di bawah       -> 4 node lama, sekali seumur hidup
 *   3. turunan dari `nama`        -> fallback, menutup objek tanpa kunci
 */
class ObjekKunci
{
    /**
     * Kunci historis untuk 4 node `objek_tarif` yang dibuat sebelum field
     * `kunci` diperkenalkan. Nilainya diambil dari `penugasan.objek_survei`
     * yang masih menyimpan taksonomi lama.
     *
     * Sifat: TETAP SEKALI SEUMUR HIDUP PROYEK, di-key berdasarkan node ID
     * Firebase yang stabil. Tidak bertambah lagi setiap ada objek baru.
     *
     * NOTE: `tarif_bus` saat ini bernama "Bus", tetapi tidak ada satu pun
     * kunci `bus` di 8.501 record survei; yang ada adalah `pick-up` (160
     * unit). Pemetaan ini sudah dikonfirmasi pemilik sistem.
     *
     * Blok ini boleh dihapus setelah keempat node punya field `kunci`.
     */
    private const PETA_LEGACY = [
        'tarif_motor' => 'motor',
        'tarif_mobil' => 'minibus',
        'tarif_bus'   => 'pick-up',
        'tarif_truk'  => 'truk',
    ];

    /**
     * Field di dalam node `survei_harian` yang BUKAN jenis kendaraan.
     * Harus dikecualikan saat menghitung "data yang tidak terpetakan".
     */
    public const FIELD_NON_KENDARAAN = ['total_survei', 'user_id'];

    /**
     * Normalisasi nama objek menjadi kunci data.
     *
     * WAJIB identik dengan OperatorController::simpanHitung() supaya kunci
     * yang dihitung di sini sama persis dengan kunci yang ditulis operator.
     * Kalau dua normalisasi ini berbeda, objek yang sama akan menghasilkan
     * dua kunci berbeda.
     */
    public static function dariNama(string $nama): string
    {
        return strtolower(str_replace(' ', '', trim($nama)));
    }

    /**
     * Kunci data untuk satu node `objek_tarif`.
     * Bisa mengandung koma bila satu objek mewakili beberapa jenis,
     * mis. nama "Bus, Pick-up" -> "bus,pick-up".
     */
    public static function untuk(string $id, array $row): string
    {
        $kunci = strtolower(trim((string)($row['kunci'] ?? '')));
        if ($kunci !== '') {
            return $kunci;
        }

        if (isset(self::PETA_LEGACY[$id])) {
            return self::PETA_LEGACY[$id];
        }

        return self::dariNama((string)($row['nama'] ?? ''));
    }

    /**
     * Kunci data milik satu node objek tarif, sudah dipecah per jenis.
     *
     * @return string[]
     */
    public static function daftarKunci(string $id, array $row): array
    {
        $parts = array_map('trim', explode(',', self::untuk($id, $row)));

        return array_values(array_filter($parts, static fn($k) => $k !== ''));
    }

    /**
     * Volume kendaraan milik satu objek tarif, diambil dari satu record
     * survei (`survei_harian/.../{id_penugasan}`).
     *
     * Ini satu-satunya tempat di kode yang tahu cara membaca jumlah
     * kendaraan dari record survei.
     */
    public static function volume(array $item, string $kunci): int
    {
        $vol = 0;
        foreach (explode(',', $kunci) as $k) {
            $k = trim($k);
            if ($k === '') {
                continue;
            }
            $vol += (int)($item[$k] ?? 0);
        }

        return $vol;
    }

    /**
     * Peta semua objek tarif, di-key dengan kunci datanya.
     *
     * @param  array $objekTarif nilai mentah dari `objek_tarif`
     * @return array<string,array{harga:int, nama:string, tarif_lama:int, id:string}>
     */
    public static function petaTarif(array $objekTarif): array
    {
        $peta = [];
        foreach ($objekTarif as $id => $row) {
            if (!is_array($row)) continue;
            $kunci = self::untuk((string)$id, $row);
            if ($kunci === '') continue;
            // Kunci tidak boleh dimiliki dua objek; yang pertama menang.
            if (isset($peta[$kunci])) continue;
            $peta[$kunci] = [
                'id'         => (string)$id,
                'nama'       => (string)($row['nama'] ?? 'Lainnya'),
                'harga'      => (int)($row['harga'] ?? 0),
                'tarif_lama' => (int)($row['tarif_lama'] ?? 0),
            ];
        }

        return $peta;
    }

    /**
     * Semua kunci kendaraan yang dimiliki objek tarif mana pun.
     *
     * @return array<string,true>
     */
    public static function kunciDimiliki(array $objekTarif): array
    {
        $dimiliki = [];
        foreach ($objekTarif as $id => $row) {
            if (!is_array($row)) continue;
            foreach (self::daftarKunci((string)$id, $row) as $k) {
                $dimiliki[$k] = true;
            }
        }

        return $dimiliki;
    }

    /**
     * Kunci kendaraan yang ADA di data survei tapi TIDAK dimiliki objek
     * tarif mana pun.
     *
     * Volume begini tidak boleh hilang diam-diam. Dulu jumlah ini lenyap
     * begitu saja hanya karena satu nama objek berubah, tanpa jejak.
     * Sekarang jumlah ini ditampilkan sebagai peringatan di laporan.
     *
     * @param  array<string,int> $volumePerKunci hasil penghitungan laporan
     * @return array<string,int> kunci => unit
     */
    public static function takTerpetakan(array $volumePerKunci, array $objekTarif): array
    {
        $dimiliki = self::kunciDimiliki($objekTarif);
        $out = [];
        foreach ($volumePerKunci as $k => $v) {
            if ((int)$v <= 0) continue;
            if (isset($dimiliki[$k])) continue;
            $out[(string)$k] = (int)$v;
        }

        return $out;
    }

    /**
     * Kunci kendaraan yang benar-benar ada di data survei, dengan
     * mengabaikan field non-kendaraan (`total_survei`, `user_id`).
     *
     * @return array<string,true>
     */
    public static function kunciSurvei(array $item): array
    {
        $kunci = [];
        foreach ($item as $k => $v) {
            if (!is_numeric($v)) continue;
            if (in_array($k, self::FIELD_NON_KENDARAAN, true)) continue;
            $kunci[(string)$k] = true;
        }

        return $kunci;
    }

    /**
     * Label tampilan untuk kunci kendaraan yang punya data tapi tidak punya
     * objek tarif.
     *
     * Sengaja ditandai, supaya operator dan admin langsung paham bahwa
     * angka itu bukan kendaraan tambahan yang nyata, melainkan data yang
     * master-nya belum sinkron. Tanpa penanda ini, kartu terlihat sama
     * dengan objek yang master-nya beres.
     *
     * Disimpan di sini, bukan di controller, karena dipakai beberapa
     * halaman laporan sekaligus dan labelnya harus sama persis di semua
     * halaman.
     */
    public static function labelBelumTerpetakan(string $kunci): string
    {
        $human = ucfirst(str_replace(['_', '-'], ' ', $kunci));

        return $human . ' (belum terpetakan)';
    }
}
