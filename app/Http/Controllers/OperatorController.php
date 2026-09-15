<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActivityLogService;

class OperatorController extends Controller
{
    protected $database;
    protected $logService;

    public function __construct(Database $database, ActivityLogService $logService)
    {
        $this->database = $database;
        $this->logService = $logService;
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
        $counts = [];
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
                    foreach ($dataTugas as $key => $val) {
                        if (in_array($key, ['user_id', 'updated_at', 'id_lokasi', 'total_survei'])) continue;
                        $counts[$key] = ($counts[$key] ?? 0) + (int)$val;
                        $totalSemua += (int)$val;
                    }

                    // Riwayat Aktivitas - Ambil per jam per penugasan milik user ini
                    if (($dataTugas['user_id'] ?? '') == $userId) {
                        $labelJam = $hour . ':00';
                        foreach ($dataTugas as $key => $val) {
                            if (in_array($key, ['user_id', 'updated_at', 'id_lokasi', 'total_survei'])) continue;
                            if ((int)$val > 0) {
                                $riwayat_kendaraan[] = [
                                    'jam' => $labelJam,
                                    'kendaraan' => ucfirst($key) . " ({$val} unit)",
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
            $penugasanAktif = $this->database->getReference('penugasan')->getValue() ?? [];
            
            foreach ($penugasanAktif as $tugas) {
                if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif' && ($tugas['id_user'] ?? '') == $userId) {
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
        
        $tugasRaw = $this->database->getReference('penugasan')->getValue() ?? [];

        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $dataLokasi = $idLokasiAktif ? ($lokasiMaster[$idLokasiAktif] ?? null) : null;

        $riwayat = [];
        foreach ($tugasRaw as $key => $tugas) {
            if (($tugas['id_user'] ?? '') != $userId) continue;
            $idLokasi = $tugas['id_lokasi'] ?? null;
            $riwayat[] = [
                'tanggal' => Carbon::parse($tugas['waktu_mulai'])->locale('id')->translatedFormat('d M Y'),
                'waktu_mulai' => $tugas['waktu_mulai'],
                'waktu_selesai' => $tugas['waktu_selesai'],
                'nama_lokasi_display' => $lokasiMaster[$idLokasi]['nama_lokasi'] ?? ($lokasiMaster[$idLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan'),
                'objek_survei' => $tugas['objek_survei'] ?? '',
                'status' => $tugas['status'] ?? 'aktif',
            ];
        }

        // Sorting: Aktif di atas, lalu berdasarkan tanggal terbaru
        usort($riwayat, function($a, $b) {
            if ($a['status'] === 'aktif' && $b['status'] !== 'aktif') return -1;
            if ($a['status'] !== 'aktif' && $b['status'] === 'aktif') return 1;
            return strtotime($b['waktu_mulai']) <=> strtotime($a['waktu_mulai']);
        });

        $counts = [];
        if ($idLokasiAktif) {
            $dataHariIni = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];
            foreach ($dataHariIni as $hour => $dataJam) {
                foreach ($dataJam as $idPenugasan => $dataTugas) {
                    foreach ($dataTugas as $key => $val) {
                        if (in_array($key, ['user_id', 'updated_at', 'id_lokasi', 'total_survei'])) continue;
                        $counts[$key] = ($counts[$key] ?? 0) + (int)$val;
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

        // 1. Ambil Data Penugasan Aktif (Milik Sendiri dan Rekan di Lokasi yang Sama)
        $semuaPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $today = $waktuSekarang->toDateString();
        
        $objekSaya = [];
        $objekRekan = []; // [ 'uid' => ['nama' => '..', 'objek' => ['..']] ]
        $idPenugasanAktif = null;
        $tugasAktifSaatIni = null;

        // Ambil info semua user untuk mapping nama rekan
        $usersMap = $this->database->getReference('users')->getValue() ?? [];

        foreach ($semuaPenugasan as $key => $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif') {
                $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                
                if ($waktuSekarang->between($mulai, $selesai)) {
                    $rawObjek = $tugas['objek_survei'] ?? '';
                    $objArr = array_filter(array_map('trim', explode(',', $rawObjek)));

                    if (($tugas['id_user'] ?? '') == $userId) {
                        $objekSaya = $objArr;
                        $tugasAktifSaatIni = $tugas;
                        $idPenugasanAktif = $key;
                    } else {
                        $uidRekan = $tugas['id_user'] ?? 'unknown';
                        $objekRekan[$uidRekan] = [
                            'nama' => $usersMap[$uidRekan]['username'] ?? 'Rekan',
                            'objek' => $objArr
                        ];
                    }
                }
            }
        }

        if (!$tugasAktifSaatIni) {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Waktu penugasan Anda sudah berakhir atau belum dimulai.');
        }

        $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
        $nama_lokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Locasi Tidak Dikenal');

        // Cek apakah sudah lapor hari ini
        $sudahLapor = isset($tugasAktifSaatIni['laporan_harian'][$today]) && $tugasAktifSaatIni['laporan_harian'][$today] == true;

        // Agregasi Data Hari Ini (Milik Sendiri dan Rekan)
        $dataHariIni = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];

        // Gabungkan semua objek unik untuk inisialisasi counter
        $semuaObjekUnik = array_unique(array_merge($objekSaya, ...array_column($objekRekan, 'objek')));
        
        $dataSurvei = ['total_survei' => 0];
        foreach ($semuaObjekUnik as $obj) {
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
            'objekSaya' => $objekSaya,
            'objekRekan' => $objekRekan,
            'idPenugasan' => $idPenugasanAktif,
            'sudahLapor' => $sudahLapor
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
            // Cek data penugasan
            $penugasan = $this->database->getReference("penugasan/{$idPenugasan}")->getValue();
            if (!$penugasan) {
                return response()->json(['success' => false, 'message' => 'Penugasan tidak ditemukan.'], 404);
            }

            // Validasi Otorisasi IDOR: Penugasan harus milik operator ini atau tugas rekan di lokasi sama yang sedang diklaim
            if (($penugasan['id_user'] ?? '') !== $userId) {
                $ownerId = $penugasan['id_user'] ?? '';
                $ownerUser = $this->database->getReference("users/{$ownerId}")->getValue();
                if (($ownerUser['claimer_id'] ?? '') !== $userId) {
                    return response()->json(['success' => false, 'message' => 'Akses penugasan tidak sah.'], 403);
                }
            }

            // Cek apakah sudah lapor hari ini (Server-side safety)
            if (isset($penugasan['laporan_harian'][$date]) && $penugasan['laporan_harian'][$date] == true) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melaporkan hasil survei hari ini. Tidak dapat menambah data.'], 403);
            }

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

            $namaLokasi = session('nama_lokasi_aktif');
            if (!$namaLokasi) {
                $locData = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
                $namaLokasi = $locData['nama_lokasi'] ?? 'Area Penugasan';
            }

            // RECORD LOG UPDATE DATA
            $this->logService->log(
                'update',
                $userId,
                $namaLokasi,
                "<strong>{$namaLokasi}</strong>: +1 " . ucfirst($request->jenis_kendaraan) . " berhasil tercatat."
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function laporSurvei(Request $request)
    {
        try {
            $idPenugasan = $request->id_penugasan;
            $userId = session('user_id');
            
            if (!$idPenugasan || !$userId) {
                return response()->json(['success' => false, 'message' => 'ID Penugasan atau sesi tidak valid'], 400);
            }

            // Validasi Otorisasi IDOR: Hanya pemilik penugasan yang bisa melapor
            $penugasan = $this->database->getReference("penugasan/{$idPenugasan}")->getValue();
            if (!$penugasan || ($penugasan['id_user'] ?? '') !== $userId) {
                return response()->json(['success' => false, 'message' => 'Otorisasi penugasan ditolak.'], 403);
            }

            // Catat bahwa operator sudah melapor untuk hari ini
            $today = Carbon::now('Asia/Makassar')->toDateString();
            $this->database->getReference("penugasan/{$idPenugasan}/laporan_harian/{$today}")->set(true);

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

    /**
     * Mengubah status istirahat operator.
     * Saat istirahat, geofencing diabaikan dan tugas bisa diklaim rekan.
     */
    public function toggleIstirahat(Request $request)
    {
        $userId = session('user_id');
        $username = session('username');
        if (!$userId) return response()->json(['success' => false], 401);

        $userRef = $this->database->getReference("users/{$userId}");
        $userData = $userRef->getValue();
        $currentStatus = $userData['status_istirahat'] ?? false;
        $newStatus = !$currentStatus;

        $updateData = ['status_istirahat' => $newStatus];
        
        // Jika kembali bekerja, hapus claimer yang membantu tadi
        if (!$newStatus) {
            $updateData['claimer_id'] = null;
        }

        $userRef->update($updateData);

        // Logging aktivitas ke admin
        $msg = $newStatus ? "mulai istirahat" : "selesai istirahat";
        $this->logService->log(
            'update',
            $userId,
            $username,
            "<strong>{$username}</strong> sedang <strong>{$msg}</strong>."
        );

        return response()->json([
            'success' => true,
            'status_istirahat' => $newStatus,
            'message' => $newStatus ? 'Status: Istirahat' : 'Status: Aktif Bekerja'
        ]);
    }

    /**
     * Mengambil alih tugas rekan yang sedang istirahat.
     */
    public function claimTugas(Request $request)
    {
        $myId = session('user_id');
        $targetId = $request->uid_rekan;

        if (!$myId || !$targetId) return response()->json(['success' => false], 400);

        $userRef = $this->database->getReference("users/{$targetId}");
        $userData = $userRef->getValue();

        // Validasi: Rekan harus sedang istirahat dan belum ada yang klaim
        if (($userData['status_istirahat'] ?? false) && empty($userData['claimer_id'])) {
            $userRef->update(['claimer_id' => $myId]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Tugas sudah diklaim atau rekan sudah aktif.']);
    }

    public function downloadPdf()
    {
        $userId = session('user_id');
        $idLokasiAktif = session('id_lokasi_aktif');
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();

        if (!$idLokasiAktif) {
            return back()->with('error', 'Silakan pilih lokasi penugasan terlebih dahulu.');
        }

        // 1. Ambil Data Penugasan Aktif
        $penugasanRaw = $this->database->getReference('penugasan')->getValue() ?? [];
        
        $tugasAktif = null;
        $idPenugasan = null;
        foreach ($penugasanRaw as $key => $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif && ($tugas['status'] ?? '') == 'aktif' && ($tugas['id_user'] ?? '') == $userId) {
                $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                if ($now->between($mulai, $selesai)) {
                    $tugasAktif = $tugas;
                    $idPenugasan = $key;
                    break;
                }
            }
        }

        if (!$tugasAktif) {
            return back()->with('error', 'Tidak ada penugasan aktif saat ini.');
        }

        // 2. Ambil Data Lokasi & User
        $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
        $user = $this->database->getReference("users/{$userId}")->getValue();
        
        // 3. Ambil Tarif Objek
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap = [];
        foreach ($tarifRaw as $t) {
            $key = strtolower(str_replace(' ', '', $t['nama']));
            $tarifMap[$key] = [
                'nama' => $t['nama'],
                'harga' => (int)($t['harga'] ?? 0),
                'tarif_lama' => (int)($t['tarif_lama'] ?? 0)
            ];
        }

        // 4. Proses Data Per Jam (Statis 08:00 - 22:00 sesuai permintaan)
        $jamMulai = 8;
        $jamSelesai = 21;
        
        $objekSurvei = array_filter(array_map('trim', explode(',', $tugasAktif['objek_survei'] ?? '')));
        $hourlyData = [];
        $summaryData = [];

        foreach ($objekSurvei as $obj) {
            $key = strtolower(str_replace(' ', '', $obj));
            
            $matchedTarif = 0;
            $matchedTarifLama = 0;
            foreach ($tarifMap as $masterKey => $masterData) {
                $masterSubKeys = explode(',', $masterKey);
                if (in_array($key, $masterSubKeys)) {
                    $matchedTarif = $masterData['harga'];
                    $matchedTarifLama = $masterData['tarif_lama'];
                    break;
                }
            }

            $summaryData[$key] = [
                'nama' => $obj,
                'jumlah' => 0,
                'tarif' => $matchedTarif,
                'tarif_lama' => $matchedTarifLama,
                'total' => 0,
                'total_lama' => 0
            ];
        }

        // Path: survei_harian/{id_lokasi}/{today}
        $dataHarian = $this->database->getReference("survei_harian/{$idLokasiAktif}/{$today}")->getValue() ?? [];

        for ($h = $jamMulai; $h <= $jamSelesai; $h++) {
            $hourKey = str_pad($h, 2, '0', STR_PAD_LEFT);
            $labelJam = $hourKey . '.00 - ' . str_pad($h + 1, 2, '0', STR_PAD_LEFT) . '.00';
            
            // Ambil data milik ID Penugasan ini di jam tersebut
            $statsJam = $dataHarian[$hourKey][$idPenugasan] ?? [];
            
            foreach ($objekSurvei as $obj) {
                $key = strtolower(str_replace(' ', '', $obj));
                $count = (int)($statsJam[$key] ?? 0);
                
                $hourlyData[$labelJam][$key] = $count;
                
                // Akumulasi ke summary
                $summaryData[$key]['jumlah'] += $count;
                $summaryData[$key]['total'] += ($count * $summaryData[$key]['tarif']);
                $summaryData[$key]['total_lama'] += ($count * $summaryData[$key]['tarif_lama']);
            }
        }

        $pdf = Pdf::loadView('operator.report_pdf', [
            'nama_lokasi' => $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal'),
            'tanggal' => $now->translatedFormat('d F Y'),
            'surveyor' => $user['username'] ?? 'Operator',
            'hourlyData' => $hourlyData,
            'summaryData' => $summaryData,
            'objekSurvei' => $objekSurvei,
            'totalSeluruh' => collect($summaryData)->sum('total'),
            'totalSeluruhLama' => collect($summaryData)->sum('total_lama'),
            'arah' => $tugasAktif['arah'] ?? '....................'
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Uji_Petik_{$today}.pdf");
    }
}
