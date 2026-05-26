<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Auth;
use Carbon\Carbon;

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
        
        // 2. Filter Lokasi yang punya Penugasan Aktif SAAT INI
        $activeLocationIds = [];
        foreach ($allPenugasan as $t) {
            if (($t['status'] ?? '') === 'aktif') {
                $mulai = Carbon::parse($t['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($t['waktu_selesai'], 'Asia/Makassar');
                
                // Jika sekarang berada di dalam rentang waktu penugasan
                if ($now->between($mulai, $selesai)) {
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
        foreach ($tarifRaw as $item) {
            $key = strtolower(str_replace(' ', '', $item['nama'] ?? ''));
            if ($key) {
                $objekNames[$key] = $item['nama'];
                $objekPrices[$key] = (int) ($item['harga'] ?? 0);
                $validKeys[] = $key;
            }
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
                                $val = (int)($stats[$key] ?? 0);
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
            $userId = session('user_id') ?? 'admin_monitor';
            $token = $this->auth->createCustomToken($userId);
            $customToken = method_exists($token, 'toString') ? $token->toString() : (string) $token;
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
