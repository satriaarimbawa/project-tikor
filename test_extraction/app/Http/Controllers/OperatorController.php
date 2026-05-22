<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class OperatorController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        $userId = session('user_id');
        $idLokasiAktif = session('id_lokasi_aktif');
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();
        
        $semuaLokasi = $this->database->getReference('lokasi')->getValue() ?? [];
        $dataLokasi = $idLokasiAktif ? ($semuaLokasi[$idLokasiAktif] ?? null) : null;
        $nama_lokasi = $dataLokasi ? ($dataLokasi['nama_lokasi'] ?? $dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal') : 'Lokasi Tidak Aktif';
        
        // 1. Ambil Ringkasan Harian & Riwayat dari struktur berjenjang jam
        $counts = ['motor' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];
        $totalSemua = 0;
        $riwayat_kendaraan = [];
        
        if ($idLokasiAktif) {
            // Path: survei_harian/{id_lokasi}/{today}
            $dataHariIni = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];
            
            // Loop Jam (00-23)
            foreach ($dataHariIni as $hour => $dataJam) {
                // Loop Penugasan dalam jam tersebut
                foreach ($dataJam as $idPenugasan => $dataTugas) {
                    // Akumulasi angka dashboard (SEMUA operator di lokasi ini)
                    foreach ($counts as $key => $val) {
                        $valTambah = $dataTugas[$key] ?? 0;
                        $counts[$key] += $valTambah;
                        $totalSemua += $valTambah;
                    }

                    // Riwayat Aktivitas - Ambil per jam per penugasan milik user ini
                    if (($dataTugas['user_id'] ?? '') == $userId) {
                        $labelJam = $hour . ':00';
                        // Karena struktur baru tidak ada node 'logs', kita ringkas per jam per jenis
                        foreach (['motor', 'minibus', 'bus', 'truk'] as $v) {
                            if (isset($dataTugas[$v]) && $dataTugas[$v] > 0) {
                                $riwayat_kendaraan[] = [
                                    'jam' => $labelJam,
                                    'kendaraan' => ucfirst($v) . " ({$dataTugas[$v]} unit)",
                                    'lokasi' => $nama_lokasi,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Urutkan riwayat (jam terbaru di atas)
        usort($riwayat_kendaraan, fn($a, $b) => strcmp($b['jam'], $a['jam']));

        $isAktif = false;
        $objekSurvei = [];
        if ($idLokasiAktif) {
            $penugasanAktif = $this->database->getReference('penugasan')
                                ->orderByChild('id_user')
                                ->equalTo($userId)
                                ->getValue() ?? [];
            
            foreach ($penugasanAktif as $tugas) {
                if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif') {
                    $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                    $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                    if ($now->between($mulai, $selesai)) {
                        $isAktif = true;
                        $rawObjek = $tugas['objek_survei'] ?? '';
                        $objekSurvei = array_filter(array_map('trim', explode(',', $rawObjek)));
                        break;
                    }
                }
            }
        }

        return view('operator.index', [
            'nama_lokasi' => $nama_lokasi,
            'riwayat_kendaraan' => $riwayat_kendaraan,
            'totalSemua' => $totalSemua,
            'counts' => $counts,
            'objekSurvei' => $objekSurvei,
            'isAktif' => $isAktif
        ]);
    }

    public function penugasan()
    {
        $userId = session('user_id');
        $idLokasiAktif = session('id_lokasi_aktif');
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();
        
        $tugasRaw = $this->database->getReference('penugasan')
                         ->orderByChild('id_user')
                         ->equalTo($userId)
                         ->getValue() ?? [];

        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $dataLokasi = $idLokasiAktif ? ($lokasiMaster[$idLokasiAktif] ?? null) : null;

        $riwayat = [];
        foreach ($tugasRaw as $key => $tugas) {
            $idLokasi = $tugas['id_lokasi'] ?? null;
            $riwayat[] = [
                'waktu_mulai' => $tugas['waktu_mulai'],
                'waktu_selesai' => $tugas['waktu_selesai'],
                'nama_lokasi_display' => $lokasiMaster[$idLokasi]['nama_lokasi'] ?? ($lokasiMaster[$idLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan'),
                'objek_survei' => $tugas['objek_survei'] ?? '',
            ];
        }

        $counts = ['motor' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];
        if ($idLokasiAktif) {
            $dataHariIni = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];
            foreach ($dataHariIni as $hour => $dataJam) {
                foreach ($dataJam as $idPenugasan => $dataTugas) {
                    foreach ($counts as $key => $val) {
                        $counts[$key] += ($dataTugas[$key] ?? 0);
                    }
                }
            }
        }

        $objekSurveiAktif = [];
        foreach ($tugasRaw as $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif') {
                $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                if ($now->between($mulai, $selesai)) {
                    $rawObjek = $tugas['objek_survei'] ?? '';
                    $objekSurveiAktif = array_filter(array_map('trim', explode(',', $rawObjek)));
                    break;
                }
            }
        }

        return view('operator.penugasan', [
            'riwayat' => $riwayat,
            'counts' => $counts,
            'objekSurvei' => $objekSurveiAktif,
            'totalSemua' => array_sum($counts),
            'namaLokasi' => $dataLokasi ? ($dataLokasi['nama_lokasi'] ?? $dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal') : 'Lokasi Tidak Aktif'
        ]);
    }

    public function survei()
    {
        $idLokasiAktif = session('id_lokasi_aktif');
        $userId = session('user_id');
        
        if (!$idLokasiAktif) {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Silakan pilih lokasi penugasan terlebih dahulu.');
        }

        $penugasanRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];
        
        $objekSurvei = [];
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $today = $waktuSekarang->toDateString();
        $tugasAktifSaatIni = null;
        $idPenugasanAktif = null;

        foreach ($penugasanRaw as $key => $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif') {
                $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                
                if ($waktuSekarang->between($mulai, $selesai)) {
                    $rawObjek = $tugas['objek_survei'] ?? '';
                    $objekSurvei = array_filter(array_map('trim', explode(',', $rawObjek)));
                    $tugasAktifSaatIni = $tugas;
                    $idPenugasanAktif = $key;
                    break;
                }
            }
        }

        if (!$tugasAktifSaatIni) {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Waktu penugasan Anda sudah berakhir atau belum dimulai.');
        }

        $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
        $nama_lokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');

        // Agregasi Data Hari Ini dari seluruh jam
        $dataHariIni = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];

        $dataSurvei = ['total_survei' => 0];
        foreach ($objekSurvei as $obj) {
            $dataSurvei[strtolower(str_replace(' ', '', $obj))] = 0;
        }

        foreach ($dataHariIni as $hour => $dataJam) {
            foreach ($dataJam as $idTugasKey => $stats) {
                foreach ($dataSurvei as $key => $val) {
                    if ($key === 'total_survei') continue;
                    $count = $stats[$key] ?? 0;
                    $dataSurvei[$key] += $count;
                    $dataSurvei['total_survei'] += $count;
                }
            }
        }

        return view('operator.survei', [
            'namaLokasi' => $nama_lokasi,
            'dataSurvei' => $dataSurvei,
            'objekSurvei' => $objekSurvei,
            'idPenugasan' => $idPenugasanAktif
        ]);
    }

    public function simpanHitung(Request $request)
    {
        $idLokasiAktif = session('id_lokasi_aktif');
        $userId = session('user_id');
        $idPenugasan = $request->id_penugasan;

        if (!$idLokasiAktif || !$userId || !$idPenugasan) {
            return response()->json(['success' => false, 'message' => 'Sesi atau ID Penugasan tidak valid'], 401);
        }

        $now = Carbon::now('Asia/Makassar');
        $date = $now->toDateString();
        $hour = $now->format('H'); // Folder JAM (00-23)
        $timeFull = $now->format('H:i:s');
        $jenis = strtolower(str_replace(' ', '', $request->jenis_kendaraan));

        try {
            // Path: survei_harian/{id_lokasi}/{date}/{hour}/{id_penugasan}
            $refPath = "survei_harian/{$idLokasiAktif}/{$date}/{$hour}/{$idPenugasan}";
            $refHarian = $this->database->getReference($refPath);
            
            $currentData = $refHarian->getValue() ?? [];

            if (empty($currentData)) {
                $currentData = [
                    'id_lokasi' => $idLokasiAktif,
                    'user_id' => $userId,
                    'total_survei' => 0
                ];
            }

            $currentData[$jenis] = (int)($currentData[$jenis] ?? 0) + 1;
            $currentData['total_survei'] = (int)($currentData['total_survei'] ?? 0) + 1;
            $currentData['updated_at'] = $timeFull;

            $refHarian->set($currentData);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function profile()
    {
        $userId = session('user_id');
        $user = $this->database->getReference("users/{$userId}")->getValue();
        
        // Tambahkan data pendukung untuk header yang konsisten
        $idLokasiAktif = session('id_lokasi_aktif');
        $nama_lokasi = 'Lokasi Tidak Aktif';
        if ($idLokasiAktif) {
            $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
            $nama_lokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');
        }

        return view('operator.profile', [
            'user' => $user,
            'nama_lokasi' => $nama_lokasi
        ]);
    }
}
