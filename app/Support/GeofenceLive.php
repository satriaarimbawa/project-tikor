<?php

namespace App\Support;

use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;

/**
 * Catatan posisi real-time operator, khusus untuk alat debugging lokal.
 *
 * LATAR BELAKANG
 *
 * Halaman peta di tools/peta/ memakai snapshot (tools/peta/peta-data.js)
 * yang diambil berkala lewat ambil_data.php. Snapshot itu tidak pernah
 * memuat koordinat operator, karena koordinat GPS tidak pernah disimpan
 * di Firebase.
 *
 * Yang terjadi di sisi browser: public/js/deteksiTikorUser.js mengirim
 * latitude/longitude tiap 60 detik ke route check.location.radius
 * (LoginController::checkLocationRadius). Server membacanya untuk
 * menghitung jarak ke lokasi tugas, lalu membuangnya. Jadi datanya ada,
 * tapi hanya sebentar di memori request.
 *
 * Kelas ini menutup celah itu dengan menuliskan posisi ke node BARU.
 * Node yang dipakai hanya dua, keduanya belum pernah ada sebelumnya:
 *
 *   geofence_live/{uid}       satu anak per operator yang denyut
 *   geofence_live_meta/jadwal penanda kapan pembersihan boleh jalan
 *
 * Tidak ada node lama yang dibaca atau ditulis, dan tidak ada node lama
 * yang dihapus. Peta lokal (tools/peta/live.php) hanya membaca kedua node
 * ini secara read-only.
 *
 * Sifat penting: kelas ini tidak pernah melempar exception. Kegagalan
 * menulis posisi tidak boleh menggagalkan heartbeat geofencing yang
 * sudah berjalan dan sedang dipakai produksi.
 */
final class GeofenceLive
{
    /** Node baru: satu anak per uid operator. */
    public const NODE = 'geofence_live';

    /**
     * Node jadwal dipisah dari NODE supaya tools/peta/live.php tidak
     * perlu memfilter kunci apa pun saat membaca. Isi NODE dijamin
     * hanya berisi uid operator.
     */
    public const NODE_META = 'geofence_live_meta';

    /** Kunci anak di dalam NODE_META. */
    public const KUNCI_JADWAL = 'jadwal';

    /** Operator tanpa heartbeat selama ini dianggap basi lalu dihapus. */
    public const UMUR_MAKS = 7200; // 2 jam

    /** Jeda minimum antar pembersihan, detik. */
    public const JEDA_BERSIHKAN = 300; // 5 menit

    public const STATUS_DALAM = 'dalam';
    public const STATUS_LUAR = 'luar';
    public const STATUS_ISTIRAHAT = 'istirahat';
    public const STATUS_TANPA_LOKASI = 'tanpa-lokasi';

    /**
     * Tulis satu denyut posisi.
     *
     * ParamETER
     *   $db      Database Firebase (sudah di-resolve di controller)
     *   $uid     id user operator dari session
     *   $posisi  data yang akan ditulis, sudah lengkap dari controller
     *
     * Nilai $posisi yang dipakai:
     *   lat, lng            koordinat dari browser (boleh 0)
     *   akurasi             akurasi GPS meter (boleh null)
     *   jarak               meter ke lokasi tugas (boleh null)
     *   radius              meter radius yang berlaku (boleh null)
     *   sumber_radius      'operator' | 'lokasi' | 'global' | null
     *   id_lokasi           id node lokasi, null kalau tidak ada tugas
     *   nama_lokasi         nama lokasi tugas, null kalau tidak ada
     *   username            untuk label di peta
     *   status              salah satu konstanta STATUS_*
     */
    public static function catat(Database $db, string $uid, array $posisi): void
    {
        try {
            $node = [
                'lat'            => self::angka($posisi['lat'] ?? null),
                'lng'            => self::angka($posisi['lng'] ?? null),
                'akurasi'        => self::angka($posisi['akurasi'] ?? null),
                'jarak'          => self::angka($posisi['jarak'] ?? null),
                'radius'         => self::angka($posisi['radius'] ?? null),
                'sumber_radius'  => $posisi['sumber_radius'] ?? null,
                'id_lokasi'      => $posisi['id_lokasi'] ?? null,
                'nama_lokasi'    => $posisi['nama_lokasi'] ?? null,
                'username'       => $posisi['username'] ?? null,
                'status'         => $posisi['status'] ?? self::STATUS_TANPA_LOKASI,
                'ts'             => Carbon::now('Asia/Makassar')->timestamp,
            ];

            $db->getReference(self::NODE . '/' . $uid)->set($node);
        } catch (\Throwable $e) {
            // Gagal menulis posisi tidak boleh menggagalkan heartbeat.
            report($e);
            return;
        }

        self::mungkinBersihkan($db);
    }

    /**
     * Bersihkan operator yang sudah lama tidak denyut.
     *
     * Dijalankan dengan peluang, bukan setiap denyut, supaya tidak
     * membaca seluruh node pada tiap permintaan. Penjaga waktu ada di
     * node meta, jadi beberapa worker sekaligus tidak akan membaca
     * node yang sama pada detik yang sama.
     */
    public static function mungkinBersihkan(Database $db): void
    {
        try {
            $refMeta = $db->getReference(self::NODE_META . '/' . self::KUNCI_JADWAL);
            $sekarang = Carbon::now('Asia/Makassar')->timestamp;
            $terakhir = $refMeta->getValue();

            if (is_numeric($terakhir) && ($sekarang - (int) $terakhir) < self::JEDA_BERSIHKAN) {
                return;
            }

            $refMeta->set($sekarang);

            $isi = $db->getReference(self::NODE)->getValue();

            if (!is_array($isi)) {
                return;
            }

            $batas = $sekarang - self::UMUR_MAKS;
            $buang = [];

            foreach ($isi as $uid => $data) {
                $ts = is_array($data) ? ($data['ts'] ?? null) : null;

                if (!is_numeric($ts) || (int) $ts < $batas) {
                    $buang[] = (string) $uid;
                }
            }

            foreach ($buang as $uid) {
                $db->getReference(self::NODE . '/' . $uid)->remove();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Status geofence dari jarak dan radius.
     *
     * Kalau radius tidak diketahui, hasilnya dianggap "dalam" supaya
     * peta tidak menandai operator sebagai/langga di luar radius hanya
     * karena datanya belum lengkap.
     */
    public static function statusDari($jarak, $radius): string
    {
        if (!is_numeric($jarak) || !is_numeric($radius)) {
            return self::STATUS_DALAM;
        }

        return ((float) $jarak > (float) $radius)
            ? self::STATUS_LUAR
            : self::STATUS_DALAM;
    }

    /**
     * Ubah nilai apa pun menjadi float, atau null kalau tidak valid.
     *
     * Firebase menyimpan 0 dan null secara berbeda, dan peta lokal
     * membedakan "koordinat 0" dari "koordinat tidak ada". Karena itu
     * nilai yang tidak bisa dipakai numerik disimpan sebagai null
     * (nilainya dihapus), bukan sebagai string kosong.
     */
    private static function angka($nilai): ?float
    {
        if ($nilai === null || $nilai === '' || is_bool($nilai)) {
            return null;
        }

        if (!is_numeric($nilai)) {
            return null;
        }

        return (float) $nilai;
    }
}