<?php

namespace Tests\Unit;

use App\Support\GeofenceLive;
use PHPUnit\Framework\TestCase;

/**
 * Penjaga untuk kelas yang mencatat posisi real-time operator.
 *
 * Yang diuji hanya keputusan murni (statusDari). Penulisan ke Firebase
 * tidak diuji di sini supaya test tidak butuh koneksi database.
 */
class GeofenceLiveTest extends TestCase
{
    public function test_node_baru_tidak_bentrok_dengan_node_lama()
    {
        // Node yang ditulis kelas ini harus node baru. Kalau suatu saat
        // ada yang mengubah nilainya ke node lama, test ini gagal.
        $this->assertSame('geofence_live', GeofenceLive::NODE);
        $this->assertSame('geofence_live_meta', GeofenceLive::NODE_META);

        // Node lama yang paling penting tidak boleh ikut disentuh.
        $nodeLama = ['lokasi', 'penugasan', 'users', 'settings/geofencing', 'notifikasi', 'activity_logs'];
        foreach ($nodeLama as $node) {
            $this->assertNotSame($node, GeofenceLive::NODE);
            $this->assertNotSame($node, GeofenceLive::NODE_META);
        }
    }

    public function test_node_posisi_tidak_bercampur_dengan_node_jadwal()
    {
        // live.php membaca NODE tanpa memfilter kunci. Jadwal harus
        // tinggal di node lain supaya tidak muncul sebagai operator.
        $this->assertNotSame(GeofenceLive::NODE, GeofenceLive::NODE_META);
        $this->assertSame('jadwal', GeofenceLive::KUNCI_JADWAL);
    }

    public function test_jarak_di_dalam_radius_berstatus_dalam()
    {
        $this->assertSame(
            GeofenceLive::STATUS_DALAM,
            GeofenceLive::statusDari(50, 100)
        );
    }

    public function test_jarak_di_tepat_radius_berstatus_dalam()
    {
        // LoginController memakai operator '>' untuk mengeluarkan
        // operator, jadi tepat sama dengan radius masih di dalam.
        $this->assertSame(
            GeofenceLive::STATUS_DALAM,
            GeofenceLive::statusDari(100, 100)
        );
    }

    public function test_jarak_di_luar_radius_berstatus_luar()
    {
        $this->assertSame(
            GeofenceLive::STATUS_LUAR,
            GeofenceLive::statusDari(100.1, 100)
        );
    }

    public function test_nilai_bukan_angka_dianggap_dalam()
    {
        // Peta lokal tidak boleh menandai operator sebagai di luar
        // radius hanya karena field belum lengkap.
        $this->assertSame(GeofenceLive::STATUS_DALAM, GeofenceLive::statusDari(null, 100));
        $this->assertSame(GeofenceLive::STATUS_DALAM, GeofenceLive::statusDari(50, null));
        $this->assertSame(GeofenceLive::STATUS_DALAM, GeofenceLive::statusDari('abc', 100));
        $this->assertSame(GeofenceLive::STATUS_DALAM, GeofenceLive::statusDari(50, 'abc'));
    }

    public function test_umur_maks_lebih_besar_dari_jeda_denyut()
    {
        // Browser mengirim heartbeat tiap 60 detik. Kalau UMUR_MAKS
        // lebih kecil dari itu, operator yang masih aktif bisa ikut
        // terhapus oleh pembersihan.
        $this->assertGreaterThanOrEqual(180, GeofenceLive::UMUR_MAKS);
    }

    public function test_jeda_pembersihan_tidak_membebani_firebase()
    {
        // Pembersihan membaca seluruh node, jadi harus berjeda.
        $this->assertGreaterThanOrEqual(60, GeofenceLive::JEDA_BERSIHKAN);
    }
}