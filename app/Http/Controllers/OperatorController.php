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
        
        // Ambil riwayat survei kendaraan (hasil_survei) untuk user ini menggunakan indeks Firebase
        $riwayatRaw = $this->database->getReference('hasil_survei')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];

        $semuaLokasi = $this->database->getReference('lokasi')->getValue() ?? [];
        $dataLokasi = $idLokasiAktif ? ($semuaLokasi[$idLokasiAktif] ?? null) : null;
        
        $nama_lokasi = $dataLokasi ? ($dataLokasi['nama_lokasi'] ?? $dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal') : 'Lokasi Tidak Aktif';
        
        $riwayat_kendaraan = [];
        $counts = ['motor' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];

        foreach ($riwayatRaw as $item) {
            $idLokasi = $item['id_lokasi'] ?? null;
            if ($idLokasi == $idLokasiAktif) {
                $jenis = strtolower(str_replace(' ', '', $item['jenis_kendaraan'] ?? ''));
                if (isset($counts[$jenis])) $counts[$jenis]++;
                else $counts[$jenis] = 1;

                $riwayat_kendaraan[] = [
                    'jam' => Carbon::parse($item['created_at'] ?? now())->format('H:i'),
                    'kendaraan' => ucfirst($item['jenis_kendaraan'] ?? 'Tidak Dikenal'),
                    'lokasi' => $semuaLokasi[$idLokasi]['nama_lokasi'] ?? ($semuaLokasi[$idLokasi]['alamat'] ?? 'Tanpa Nama'),
                ];
            }
        }

        // Urutkan riwayat terbaru di atas
        usort($riwayat_kendaraan, fn($a, $b) => strcmp($b['jam'], $a['jam']));

        $isAktif = false;
        $objekSurvei = [];
        if ($idLokasiAktif) {
            $waktuSekarang = Carbon::now('Asia/Makassar');
            $penugasanAktif = $this->database->getReference('penugasan')
                                ->orderByChild('id_user')
                                ->equalTo($userId)
                                ->getValue() ?? [];
            
            foreach ($penugasanAktif as $tugas) {
                if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif) {
                    $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                    $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                    if ($waktuSekarang->between($mulai, $selesai)) {
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
            'totalSemua' => count($riwayat_kendaraan),
            'counts' => $counts,
            'objekSurvei' => $objekSurvei,
            'isAktif' => $isAktif
        ]);
    }

    public function penugasan()
    {
        $userId = session('user_id');
        $idLokasiAktif = session('id_lokasi_aktif');
        
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
                'objek_survei' => $tugas['objek_survei'] ?? '', // Tambahkan ini
            ];
        }

        // --- TAMBAHAN UNTUK AKTIFKAN TOTAL SURVEY ---
        $surveiRaw = $this->database->getReference('hasil_survei')
                          ->orderByChild('id_user')
                          ->equalTo($userId)
                          ->getValue() ?? [];

        $counts = ['motor' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];
        foreach ($surveiRaw as $item) {
            if (($item['id_lokasi'] ?? '') == $idLokasiAktif) {
                $jenis = strtolower(str_replace(' ', '', $item['jenis_kendaraan'] ?? ''));
                if (isset($counts[$jenis])) $counts[$jenis]++;
            }
        }

        // Ambil objek survei yang aktif saat ini untuk ditampilkan di icon atas
        $objekSurveiAktif = [];
        $waktuSekarang = Carbon::now('Asia/Makassar');
        foreach ($tugasRaw as $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif) {
                $mulai = Carbon::parse($tugas['waktu_mulai'], 'Asia/Makassar');
                $selesai = Carbon::parse($tugas['waktu_selesai'], 'Asia/Makassar');
                if ($waktuSekarang->between($mulai, $selesai)) {
                    $rawObjek = $tugas['objek_survei'] ?? '';
                    $objekSurveiAktif = array_filter(array_map('trim', explode(',', $rawObjek)));
                    break;
                }
            }
        }

        return view('operator.penugasan', [
            'riwayat' => $riwayat,
            'counts' => $counts,
            'objekSurvei' => $objekSurveiAktif, // Tambahkan ini
            'totalSemua' => count($riwayat), // Berpatokan pada jumlah riwayat tugas
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

        // 1. Ambil penugasan aktif untuk user ini
        $penugasanRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];
        
        $objekSurvei = [];
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $tugasAktifSaatIni = null;
        $idPenugasanAktif = null;

        foreach ($penugasanRaw as $key => $tugas) {
            if (($tugas['id_lokasi'] ?? '') == $idLokasiAktif) {
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

        // 2. Ambil SEMUA hasil survei di lokasi ini dalam rentang waktu penugasan (SEMUA OPERATOR)
        $surveiLokasiRaw = $this->database->getReference('hasil_survei')
                          ->orderByChild('id_lokasi')
                          ->equalTo($idLokasiAktif)
                          ->getValue() ?? [];

        $dataSurvei = ['total_survei' => 0];
        foreach ($objekSurvei as $obj) {
            $dataSurvei[strtolower(str_replace(' ', '', $obj))] = 0;
        }

        $mulaiTugas = Carbon::parse($tugasAktifSaatIni['waktu_mulai'], 'Asia/Makassar');
        $selesaiTugas = Carbon::parse($tugasAktifSaatIni['waktu_selesai'], 'Asia/Makassar');

        foreach ($surveiLokasiRaw as $item) {
            $waktuInput = Carbon::parse($item['created_at'], 'Asia/Makassar');
            // Cek apakah input terjadi di dalam jam tugas ini
            if ($waktuInput->between($mulaiTugas, $selesaiTugas)) {
                $jenis = strtolower(str_replace(' ', '', $item['jenis_kendaraan'] ?? ''));
                if (isset($dataSurvei[$jenis])) {
                    $dataSurvei[$jenis]++;
                    $dataSurvei['total_survei']++;
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

    public function laporSurvei(Request $request)
    {
        $idPenugasan = $request->id_penugasan;
        $userId = session('user_id');
        $username = session('username');

        if (!$idPenugasan) {
            return response()->json(['success' => false, 'message' => 'ID Penugasan tidak ditemukan'], 400);
        }

        $waktuSekarang = Carbon::now('Asia/Makassar')->format('Y-m-d H:i');
        
        try {
            // 1. Ambil data penugasan sebelum diupdate untuk info pesan
            $tugas = $this->database->getReference("penugasan/{$idPenugasan}")->getValue();
            $idLokasi = $tugas['id_lokasi'] ?? '';
            $dataLokasi = $this->database->getReference("lokasi/{$idLokasi}")->getValue();
            $namaLokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Unknown');

            // 2. Update status penugasan menjadi selesai
            $this->database->getReference("penugasan/{$idPenugasan}")
                 ->update(['waktu_selesai' => $waktuSekarang]);
            
            // 3. Catat sebagai Pesan Masuk (Notifikasi) untuk Admin
            $notifData = [
                'id_user' => $userId,
                'username' => $username,
                'id_lokasi' => $idLokasi,
                'nama_lokasi' => $namaLokasi,
                'pesan' => "Laporan Survei Selesai di {$namaLokasi}",
                'waktu' => $waktuSekarang,
                'status' => 'unread'
            ];
            
            $this->database->getReference('notifikasi')->push($notifData);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function simpanHitung(Request $request)
    {
        $idLokasiAktif = session('id_lokasi_aktif');
        $userId = session('user_id');

        if (!$idLokasiAktif || !$userId) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid'], 401);
        }

        $data = [
            'id_user' => $userId,
            'id_lokasi' => $idLokasiAktif,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'created_at' => Carbon::now('Asia/Makassar')->toDateTimeString()
        ];

        $this->database->getReference('hasil_survei')->push($data);

        return response()->json(['success' => true]);
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
