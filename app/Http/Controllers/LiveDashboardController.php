<?php

namespace App\Http\Controllers;

use App\Support\ObjekKunci;
use App\Support\PenugasanWaktu;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

/**
 * Live Monitoring: ringkasan volume kendaraan hari ini per lokasi.
 *
 * Aturan pencocokan objek: tarif dicocokkan lewat `ObjekKunci`, bukan lewat
 * `objek_tarif.nama`. Kalau ikut memakai `nama`, begitu admin me-rename
 * objek di master, kunci turunan ikut berubah dan seluruh volume historis
 * objek itu lenyap tanpa error. Rinciannya ada di
 * `App\Support\ObjekKunci`.
 */
class LiveDashboardController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        Carbon::setLocale('id');
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();

        // 1. Ambil Data Master & Penugasan
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $allLokasi = $this->database->getReference('lokasi')->getValue() ?? [];
        $allPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
        
        // 2. Filter Lokasi yang sedang ditugaskan.
        // Aturan "sedang berjalan" (waktu sudah masuk rentang DAN belum
        // dihentikan admin) dihitung di satu tempat, yaitu
        // App\Support\PenugasanWaktu, supaya tidak berbeda beda antar
        // halaman.
        //
        // Kode lama juga menuntut waktu_mulai ber-tanggal hari ini.
        // Syarat itu dihapus: penugasan yang melintang tengah malam
        // tetap berjalan, dan data survei jam-jam awal hari ini
        // tetap dihitung di bawah. Dulu lokasi seperti itu ikut hilang
        // dari dashboard tepat setelah pukul 00:00.
        $activeLocationIds = PenugasanWaktu::idLokasiBerjalan($allPenugasan, $now);

        // Bangun lokasiMaster hanya untuk lokasi yang aktif ditugaskan
        $lokasiMaster = [];
        foreach ($activeLocationIds as $idL) {
            if (isset($allLokasi[$idL])) {
                $lokasiMaster[$idL] = $allLokasi[$idL];
            }
        }
        
        $objekNames = [];
        $objekPrices = [];
        $validKeys = [];
        foreach ($tarifRaw as $itemId => $item) {
            if (!is_array($item)) {
                continue;
            }
            $key = ObjekKunci::untuk((string) $itemId, $item);
            if ($key === '' || isset($objekNames[$key])) {
                continue;
            }
            $objekNames[$key] = (string) ($item['nama'] ?? 'Lainnya');
            $objekPrices[$key] = (int) ($item['harga'] ?? 0);
            $validKeys[] = $key;
        }

        // 3. Ambil Data Survei Hari Ini (Initial Load)
        $surveiHarianRaw = $this->database->getReference('survei_harian')->getValue() ?? [];

        // 3a. Lintasan pertama: kumpulkan dulu semua kunci kendaraan yang
        // benar-benar ada di record hari ini.
        //
        // Ini existensial. Penghitungan di bawah hanya mengiterasi kunci
        // dari master `objek_tarif`, sehingga kunci yang PUNYA data tapi
        // tidak dimiliki master mana pun akan dibuang tanpa jejak: tidak
        // ada angkanya, tidak ada kolomnya, tidak ada peringatannya.
        //motor adalah contoh yang paling sering kena: begitu node
        // `tarif_motor` dihapus admin, atau field `kunci`-nya diisi nilai
        // yang tidak lagi sama dengan kunci historis `motor`, seluruh
        // volume motor yang sudah tercatat lenyap dari semua halaman.
        //
        // Kuncinya dibaca dari data, bukan dari master, jadi aman terhadap
        // kedua kemungkinan itu. Field non-kendaraan (`total_survei`,
        // `user_id`) otomatis dilewati oleh `kunciSurvei()`.
        $volumePerKunci = [];
        foreach ($surveiHarianRaw as $dataTanggal) {
            if (!is_array($dataTanggal) || !isset($dataTanggal[$today]) || !is_array($dataTanggal[$today])) {
                continue;
            }
            foreach ($dataTanggal[$today] as $dataJam) {
                if (!is_array($dataJam)) {
                    continue;
                }
                foreach ($dataJam as $stats) {
                    if (!is_array($stats)) {
                        continue;
                    }
                    foreach (ObjekKunci::kunciSurvei($stats) as $fieldKey => $ignored) {
                        $volumePerKunci[$fieldKey] = ($volumePerKunci[$fieldKey] ?? 0) + (int) $stats[$fieldKey];
                    }
                }
            }
        }

        // 3b. Kunci yang punya data tapi tidak punya objek tarif. Volumenya
        // tetap ikut dihitung dan ikut ditampilkan, hanya diberi label
        // "belum terpetakan" supaya jelas itu masalah master, bukan
        // kendaraan tambahan yang nyata.
        //
        // Harganya 0 karena tarifnya memang tidak diketahui, sehingga tidak
        // ada pendapatan yang dikarang. Yang hilang cuma angka kendaraan, bukan
        // kesesuan keuangan.
        $unmappedKeys = [];
        foreach (ObjekKunci::takTerpetakan($volumePerKunci, $tarifRaw) as $uKey => $uVol) {
            $uKey = (string) $uKey;
            if (isset($objekNames[$uKey])) {
                continue;
            }
            $unmappedKeys[$uKey] = (int) $uVol;
            $objekNames[$uKey] = ObjekKunci::labelBelumTerpetakan($uKey);
            $objekPrices[$uKey] = 0;
            $validKeys[] = $uKey;
        }

        $initialGlobal = array_fill_keys($validKeys, 0);
        $initialLokasi = [];
        foreach ($lokasiMaster as $idL => $loc) $initialLokasi[$idL] = 0;

        foreach ($surveiHarianRaw as $idLokasi => $dataTanggal) {
            if (isset($dataTanggal[$today]) && is_array($dataTanggal[$today])) {
                foreach ($dataTanggal[$today] as $hour => $dataJam) {
                    foreach ($dataJam as $idKey => $stats) {
                        if (is_array($stats)) {
                            foreach ($validKeys as $key) {
                                $val = ObjekKunci::volume($stats, $key);

                                $initialGlobal[$key] += $val;
                                if (isset($initialLokasi[$idLokasi])) {
                                    $initialLokasi[$idLokasi] += $val;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 4. Ambil Daftar User untuk Presence Monitoring
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        $operators = [];
        
        // Buat map ID Lokasi ke Nama Lokasi untuk mempermudah pencarian
        $lokasiNamesMap = [];
        foreach ($allLokasi as $idL => $loc) {
            $lokasiNamesMap[$idL] = $loc['nama_lokasi'] ?? ($loc['alamat'] ?? 'Lokasi');
        }

        foreach ($usersRaw as $uid => $u) {
            // Sembunyikan akun IT Support agar tidak tampil di Live Monitoring
            if (($u['username'] ?? '') === 'IT Support' || strpos(strtolower($u['email'] ?? ''), 'itsupport') !== false) {
                continue;
            }
            
            $role = $u['role_user'] ?? 'user';
            $locationName = 'Tanpa Lokasi';

            if ($role === 'operator') {
                // Cari lokasi yang sedang ditugaskan ke user ini.
                $locationName = PenugasanWaktu::namaLokasiAktif(
                    $allPenugasan,
                    (string) $uid,
                    $lokasiNamesMap,
                    $now
                ) ?? $locationName;
            } else {
                $locationName = ucfirst($role);
            }

            $operators[$uid] = [
                'username' => $u['username'] ?? 'User',
                'is_online' => $u['is_online'] ?? false,
                'role' => $role,
                'location' => $locationName
            ];
        }

        return view('admin.dashboard_live', [
            'objekNames' => $objekNames,
            'objekPrices' => $objekPrices,
            'unmappedKeys' => $unmappedKeys,
            'lokasiMaster' => $lokasiMaster,
            'initialGlobal' => $initialGlobal,
            'initialLokasi' => $initialLokasi,
            'totalGlobal' => array_sum($initialGlobal),
            'operators' => $operators,
            'lokasiNamesMap' => $lokasiNamesMap,
            'firebaseConfig' => config('firebase.projects.app')
        ]);
    }
}
