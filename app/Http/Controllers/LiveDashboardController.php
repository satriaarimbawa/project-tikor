<?php

namespace App\Http\Controllers;

use App\Support\ObjekKunci;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Auth;
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
    protected $auth;

    public function __construct(Database $database, Auth $auth)
    {
        $this->database = $database;
        $this->auth = $auth;
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
        
        // 2. Filter Lokasi yang punya Penugasan HARI INI
        $activeLocationIds = [];
        foreach ($allPenugasan as $t) {
            if (!empty($t['waktu_mulai'])) {
                $mulai = Carbon::parse($t['waktu_mulai'], 'Asia/Makassar')->toDateString();
                if ($mulai === $today) {
                    $activeLocationIds[] = $t['id_lokasi'] ?? '';
                }
            }
        }
        $activeLocationIds = array_unique(array_filter($activeLocationIds));

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

        // 3. Generate Custom Token
        $customToken = null;
        try {
            $userId = session('user_id');
            if ($userId) {
                $token = $this->auth->createCustomToken($userId);
                $customToken = method_exists($token, 'toString') ? $token->toString() : (string) $token;
            }
        } catch (\Exception $e) {}

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
                // Cari lokasi aktif user ini di tabel penugasan
                foreach ($allPenugasan as $t) {
                    if (($t['id_user'] ?? '') == $uid && ($t['status'] ?? '') === 'aktif') {
                        $mulai = Carbon::parse($t['waktu_mulai'], 'Asia/Makassar');
                        $selesai = Carbon::parse($t['waktu_selesai'], 'Asia/Makassar');
                        
                        if ($now->between($mulai, $selesai)) {
                            $idL = $t['id_lokasi'] ?? '';
                            $locationName = $lokasiNamesMap[$idL] ?? 'Lokasi Aktif';
                            break;
                        }
                    }
                }
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
            'lokasiMaster' => $lokasiMaster,
            'initialGlobal' => $initialGlobal,
            'initialLokasi' => $initialLokasi,
            'totalGlobal' => array_sum($initialGlobal),
            'operators' => $operators,
            'lokasiNamesMap' => $lokasiNamesMap,
            'firebaseConfig' => config('firebase.projects.app'),
            'firebaseToken' => $customToken,
            'firebaseApiKey' => 'AIzaSyA_raJzGxDNyvpn1OIFczKdB6I-mpdTYdI'
        ]);
    }
}
