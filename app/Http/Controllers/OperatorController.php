<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActivityLogService;
use App\Support\ObjekKunci;
use App\Support\PenugasanWaktu;

/**
 * Halaman dan aksi untuk petugas lapangan (operator).
 *
 * File ini memegang JALUR TULIS ke data survei, jadi aturan paling penting
 * ada di sini:
 *
 *   Nama objek kendaraan yang masuk ke `survei_harian` SELALU dinormalisasi
 *   lewat `ObjekKunci::dariNama()`. Jangan pernah menulis `strtolower()` atau
 *   `str_replace()` sendiri di file ini. Kalau normalisasi di jalur tulis dan
 *   di jalur baca laporan berbeda, satu jenis kendaraan bisa tersimpan
 *   dengan dua kunci yang berbeda, dan datanya terbelah tanpa error.
 *
 * Rincian masalah dan strateginya ada di `App\Support\ObjekKunci`.
 */
class OperatorController extends Controller
{
    /** Koneksi Firebase Realtime Database. */
    protected $database;

    /** Pencatat jejak aktivitas (audit log). */
    protected $logService;

    /**
     * @param Database          $database  Injeksi dari container Laravel.
     * @param ActivityLogService $logService Injeksi dari container Laravel.
     */
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
            $penugasanAktif = $this->database->getReference('penugasan')
                                ->orderByChild('id_user')
                                ->equalTo($userId)
                                ->getValue() ?? [];
            
            $cocok = PenugasanWaktu::cari(
                $penugasanAktif,
                static fn (array $tugas): bool => (string) ($tugas['id_lokasi'] ?? '') === (string) $idLokasiAktif,
                $now
            );

            if ($cocok !== null) {
                $isAktif = true;
                $objekSurvei = PenugasanWaktu::objekSurvei($cocok[PenugasanWaktu::KUNCI_TUGAS]);
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

        $cocok = PenugasanWaktu::cari(
            $tugasRaw,
            static fn (array $tugas): bool => (string) ($tugas['id_lokasi'] ?? '') === (string) $idLokasiAktif,
            $now
        );

        $objekSurveiAktif = $cocok === null
            ? []
            : PenugasanWaktu::objekSurvei($cocok[PenugasanWaktu::KUNCI_TUGAS]);

        return view('operator.penugasan', [
            'riwayat' => $riwayat,
            'counts' => $counts,
            'objekSurvei' => $objekSurveiAktif,
            'totalSemua' => array_sum($counts),
            'namaLokasi' => $dataLokasi ? ($dataLokasi['nama_lokasi'] ?? $dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal') : 'Lokasi Tidak Aktif'
        ]);
    }

    /**
     * Form hitung Survei: menampilkan input angka per jenis kendaraan.
     *
     * Angka yang tampil adalah agregasi hari ini untuk lokasi aktif, baik
     * milik operator sendiri maupun milik rekan yang sedang bekerja di lokasi
     * yang sama. Field `total_survei` adalah SKALAR total, bukan salah satu
     * kategori kendaraan, jadi harus dikecualikan saat mengiterasi kunci
     * (lihat `ObjekKunci::FIELD_NON_KENDARAAN`).
     *
     * Kunci counter dibangun dari NAMA objek yang tersimpan di
     * `penugasan.objek_survei`, yaitu taksonomi LAMA yang sudah beku sejak
     * penugasan dibuat. Nama itu lalu dinormalisasi dengan
     * `ObjekKunci::dariNama()` supaya persis sama dengan kunci yang ditulis
     * `simpanHitung()` dan yang dibaca laporan.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
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

        $berjalan = PenugasanWaktu::semua(
            $semuaPenugasan,
            static fn (array $tugas): bool => (string) ($tugas['id_lokasi'] ?? '') === (string) $idLokasiAktif,
            $waktuSekarang
        );

        foreach ($berjalan as $entri) {
            $tugas = $entri[PenugasanWaktu::KUNCI_TUGAS];

            if ((string) ($tugas['id_user'] ?? '') === (string) $userId) {
                $objekSaya = PenugasanWaktu::objekSurvei($tugas);
                $tugasAktifSaatIni = $tugas;
                $idPenugasanAktif = $entri[PenugasanWaktu::KUNCI_KEY];
            } else {
                $uidRekan = $tugas['id_user'] ?? 'unknown';
                $objekRekan[$uidRekan] = [
                    'nama' => $usersMap[$uidRekan]['username'] ?? 'Rekan',
                    'objek' => PenugasanWaktu::objekSurvei($tugas)
                ];
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
            $dataSurvei[ObjekKunci::dariNama((string)$obj)] = 0;
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

    /**
     * Menyimpan satu angka hitung dari form Survei.
     *
     * Menulis ke `survei_harian/{lokasi}/{tanggal}/{jam}/{penugasan}` dengan
     * operasi INCREMENT pada kunci kendaraan, bukan menimpa.
     *
     * Pemeriksaan yang dilakukan sebelum menulis:
     *   - sesi lokasi/user/penugasan harus ada;
     *   - penugasan harus ada;
     *   - otorisasi IDOR: penugasan milik sendiri, atau milik rekan yang
     *     sedang diklaim operator ini di lokasi yang sama;
     *   - operator belum lapor hari ini.
     *
     * @param Request $request Mengandung `id_penugasan` dan `jenis_kendaraan`
     *        (nama objek mentah dari form, BUKAN kunci yang sudah ternormalisasi).
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Normalisasi WAJIB lewat ObjekKunci::dariNama() supaya kunci yang
        // ditulis ke survei_harian identik dengan kunci yang dibaca semua
        // laporan. Kalau dua normalisasi ini berbeda, satu objek bisa
        // menghasilkan dua kunci dan datanya terbelah.
        $jenis = ObjekKunci::dariNama((string)$request->jenis_kendaraan);

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

    /**
     * Rekap hasil survei penugasan aktif hari ini, format PDF (A4 portrait).
     *
     * Menampilkan matriks jam (08:00-21:00) x jenis kendaraan. Tarif diambil
     * dari master `objek_tarif` dengan mencocokkan KUNCI DATA, bukan nama,
     * sehingga me-rename objek di master tidak membuat tarif jadi nol.
     *
     * Kolom `total` memakai tarif BERLAKUA, kolom `total_lama` memakai
     * `tarif_lama`, supaya selisih kenaikan tarif terlihat.
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
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
        $penugasanRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];
        
        $cocok = PenugasanWaktu::cari(
            $penugasanRaw,
            static fn (array $tugas): bool => (string) ($tugas['id_lokasi'] ?? '') === (string) $idLokasiAktif,
            $now
        );

        $tugasAktif  = $cocok[PenugasanWaktu::KUNCI_TUGAS] ?? null;
        $idPenugasan = $cocok[PenugasanWaktu::KUNCI_KEY] ?? null;

        if (!$tugasAktif) {
            return back()->with('error', 'Tidak ada penugasan aktif saat ini.');
        }

        // 2. Ambil Data Lokasi & User
        $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
        $user = $this->database->getReference("users/{$userId}")->getValue();
        
        // 3. Ambil Tarif Objek
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap = [];
        foreach ($tarifRaw as $tId => $t) {
            if (!is_array($t)) continue;
            $key = ObjekKunci::untuk((string)$tId, $t);
            $tarifMap[$key] = [
                'nama' => (string)($t['nama'] ?? 'Lainnya'),
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
            $key = ObjekKunci::dariNama((string)$obj);

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
                $key = ObjekKunci::dariNama((string)$obj);
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
