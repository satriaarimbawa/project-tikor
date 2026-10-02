<?php

namespace App\Http\Controllers;

use App\Support\GeofenceLive;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Database;

/**
 * Peta posisi real-time operator, khusus IT Support.
 *
 * TUJUAN
 *
 * Halaman ini alat bantu debug, bukan alat operasional lapangan.
 * Yang ditampilkan hanya posisi terakhir yang dikirim operator lewat
 * heartbeat geofencing (App\Support\GeofenceLive). Tidak ada riwayat
 * perjalanan, tidak ada tombol kendali, tidak ada pencatatan aktivitas
 * baru.
 *
 * BATASAN KEAMANAN
 *
 * Peta ini memakai route JSON di server, bukan listener Firebase di
 * browser. Alasannya: database.rules.json membuka .read untuk beberapa
 * node, dan koordinat GPS operator tidak boleh bisa dibaca oleh siapa
 * pun yang sekadar tahu URL. Dengan route ini, koordinat hanya keluar
 * setelah middleware it_support lulus. Konsekuensinya, rules Firebase
 * tidak perlu diubah sama sekali untuk fitur ini.
 *
 * Tanpa alasan keamanan yang kuat, browser tidak boleh menyentuh
 * Firebase langsung untuk node ini. Jangan menghemat satu request
 * dengan menulis listener di browser.
 *
 * SELURUH AKSES DI SINI READ-ONLY
 *
 * Controller ini hanya memanggil getValue(). Tidak ada set, update,
 * remove, push, atau transaction. Memberi izin tulis di sini berarti
 * satu klik salah bisa mengubah data produksi.
 *
 * NODE YANG DIBACA
 *
 *   geofence_live/{uid}  posisi denyut terakhir per operator
 *   users                daftar operator (nama dan status online)
 *   lokasi               titik pos uji (marker acuan di peta)
 *
 * Ketiganya sudah ada di produksi dan tidak pernah diubah oleh fitur ini.
 * Tidak ada node baru yang dibaca.
 */
class PetaOperatorController extends Controller
{
    /** Batas kesegaran denyut, detik. Sama dengan GeofenceLive::UMUR_MAKS. */
    private const UMUR_BASI = 7200;

    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Halaman peta. Semua data sudah dimuat server supaya halaman tetap
     * tampil utuh walau request penyegaran berikutnya gagal.
     */
    public function index()
    {
        $sekarang = Carbon::now('Asia/Makassar')->timestamp;

        try {
            $liveRaw   = $this->database->getReference(GeofenceLive::NODE)->getValue() ?? [];
            $usersRaw  = $this->database->getReference('users')->getValue() ?? [];
            $lokasiRaw = $this->database->getReference('lokasi')->getValue() ?? [];
            $gagalBaca = false;
        } catch (\Throwable $e) {
            report($e);
            $liveRaw = $usersRaw = $lokasiRaw = [];
            $gagalBaca = true;
        }

        return view('admin.peta_operator', [
            'operator'  => $this->susunOperator($liveRaw, $usersRaw, $sekarang),
            'lokasi'    => $this->susunLokasi($lokasiRaw),
            'umurBasi'  => self::UMUR_BASI,
            'gagalBaca' => $gagalBaca,
        ]);
    }

    /**
     * Route JSON untuk penyegaran berkala.
     *
     * Hanya getValue(). Kalau Firebase tidak terbaca, route tetap balas
     * JSON dengan pesan kesalahan supaya halaman bisa menampilkan
     * keterangan, bukan tampil kosong tanpa alasan.
     */
    public function posisi()
    {
        $sekarang = Carbon::now('Asia/Makassar')->timestamp;

        try {
            $liveRaw   = $this->database->getReference(GeofenceLive::NODE)->getValue() ?? [];
            $usersRaw  = $this->database->getReference('users')->getValue() ?? [];
            $lokasiRaw = $this->database->getReference('lokasi')->getValue() ?? [];

            return response()->json([
                'ok'        => true,
                'waktu'     => Carbon::now('Asia/Makassar')->format('d/m/Y H:i:s'),
                'umur_basi' => self::UMUR_BASI,
                'operator'  => $this->susunOperator($liveRaw, $usersRaw, $sekarang),
                'lokasi'    => $this->susunLokasi($lokasiRaw),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok'       => false,
                'pesan'    => 'Gagal membaca Firebase. Periksa koneksi dan kredensial service account.',
                'operator' => [],
                'lokasi'   => [],
            ], 200);
        }
    }

    /**
     * Gabungkan denyut posisi dengan daftar operator.
     *
     * Operator yang online tapi tidak punya denyut tetap ikut ditampilkan.
     * Kasus itu justru yang paling dicari IT Support saat debug: denyut
     * ditolak server, jadi tidak ada koordinat sama sekali.
     */
    private function susunOperator($liveRaw, $usersRaw, int $sekarang): array
    {
        $liveRaw  = is_array($liveRaw) ? $liveRaw : [];
        $usersRaw = is_array($usersRaw) ? $usersRaw : [];

        // Kunci uid yang harus selalu tampil: operator lebih dulu, baru
        // uid apa pun yang muncul di node denyut.
        $daftarUid = [];

        foreach ($usersRaw as $uid => $u) {
            if (is_array($u) && ($u['role_user'] ?? '') === 'operator') {
                $daftarUid[] = (string) $uid;
            }
        }

        foreach (array_keys($liveRaw) as $uid) {
            $daftarUid[] = (string) $uid;
        }

        $daftarUid = array_values(array_unique($daftarUid));
        sort($daftarUid);

        $hasil = [];

        foreach ($daftarUid as $uid) {
            $user   = is_array($usersRaw[$uid] ?? null) ? $usersRaw[$uid] : [];
            $denyut = is_array($liveRaw[$uid] ?? null) ? $liveRaw[$uid] : [];

            $hasil[] = $this->susunSatu($uid, $user, $denyut, $sekarang);
        }

        return $hasil;
    }

    /**
     * Satu baris operator untuk peta.
     */
    private function susunSatu(string $uid, array $user, array $denyut, int $sekarang): array
    {
        $lat = $this->koordinat($denyut['lat'] ?? null, -90, 90);
        $lng = $this->koordinat($denyut['lng'] ?? null, -180, 180);
        $ts  = $denyut['ts'] ?? null;

        $umur = (is_numeric($ts) && (int) $ts > 0)
            ? max(0, $sekarang - (int) $ts)
            : null;

        // Denyut basi berarti operator sudah lama tidak mengirim posisi.
        // Penandanya dibiarkan tampil supaya IT Support tidak salah baca
        // sebagai posisi sekarang, dan dibedakan dari operator yang denyutnya
        // ditolak karena tidak ada koordinat sama sekali.
        $basi = $umur !== null && $umur > self::UMUR_BASI;

        $status = (string) ($denyut['status'] ?? GeofenceLive::STATUS_TANPA_LOKASI);

        if ($lat === null || $lng === null) {
            $status = GeofenceLive::STATUS_TANPA_LOKASI;
        }

        return [
            'uid'           => $uid,
            'username'      => (string) ($denyut['username'] ?? $user['username'] ?? $uid),
            'is_online'     => (bool) ($user['is_online'] ?? false),
            'lat'           => $lat,
            'lng'           => $lng,
            'akurasi'       => $this->angka($denyut['akurasi'] ?? null),
            'jarak'         => $this->angka($denyut['jarak'] ?? null),
            'radius'        => $this->angka($denyut['radius'] ?? null),
            'sumber_radius' => $denyut['sumber_radius'] ?? null,
            'id_lokasi'     => isset($denyut['id_lokasi']) ? (string) $denyut['id_lokasi'] : null,
            'nama_lokasi'   => isset($denyut['nama_lokasi']) ? (string) $denyut['nama_lokasi'] : null,
            'status'        => $status,
            'umur'          => $umur,
            'basi'          => $basi,
        ];
    }

    /**
     * Titik pos uji untuk marker acuan.
     *
     * Node lokasi sudah punya latitude dan longitude terpisah. String
     * koordinat hanya dipakai kalau salah satu field terpisah kosong,
     * karena tidak semua node lama menyimpan kedua field itu.
     */
    private function susunLokasi($lokasiRaw): array
    {
        if (!is_array($lokasiRaw)) {
            return [];
        }

        $hasil = [];

        foreach ($lokasiRaw as $id => $loc) {
            if (!is_array($loc)) {
                continue;
            }

            $lat = $this->koordinat($loc['latitude'] ?? null, -90, 90);
            $lng = $this->koordinat($loc['longitude'] ?? null, -180, 180);

            if ($lat === null || $lng === null) {
                $pecah = $this->pecahKoordinat($loc['koordinat'] ?? null);
                $lat = $lat ?? $pecah[0];
                $lng = $lng ?? $pecah[1];
            }

            if ($lat === null || $lng === null) {
                continue;
            }

            $hasil[] = [
                'id'     => (string) $id,
                'nama'   => (string) ($loc['nama_lokasi'] ?? $id),
                'lat'    => $lat,
                'lng'    => $lng,
                'radius' => $this->angka($loc['radius'] ?? null),
            ];
        }

        return $hasil;
    }

    /**
     * Pecah string "lat,lng".
     *
     * @return array{0: ?float, 1: ?float}
     */
    private function pecahKoordinat($teks): array
    {
        if (!is_string($teks) || strpos($teks, ',') === false) {
            return [null, null];
        }

        $bagian = array_map('trim', explode(',', $teks, 2));

        return [
            $this->koordinat($bagian[0] ?? null, -90, 90),
            $this->koordinat($bagian[1] ?? null, -180, 180),
        ];
    }

    /**
     * Ubah nilai apa pun jadi float, atau null kalau tidak bisa dipakai.
     */
    private function angka($nilai): ?float
    {
        if ($nilai === null || $nilai === '' || is_bool($nilai) || !is_numeric($nilai)) {
            return null;
        }

        return (float) $nilai;
    }

    /**
     * Koordinat yang layak ditampilkan di peta.
     *
     * 0,0 adalah titik di teluk Guinea, bukan posisi operator di Bali,
     * jadi nilai 0 dan 0 dianggap tidak punya posisi.
     */
    private function koordinat($nilai, float $bawah, float $atas): ?float
    {
        $nilai = $this->angka($nilai);

        if ($nilai === null || $nilai < $bawah || $nilai > $atas) {
            return null;
        }

        if ($nilai == 0.0) {
            return null;
        }

        return $nilai;
    }
}