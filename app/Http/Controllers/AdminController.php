<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function loginadmin()
    {
        return view('login.logadmin');
    }

    public function index()
    {
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $hariIni = $waktuSekarang->toDateString();

        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap = [];
        $stats = [];
        $chartData = [];
        $objekNames = [];

        foreach ($tarifRaw as $item) {
            $namaOriginal = $item['nama'] ?? 'Lainnya';
            $key = strtolower(str_replace(' ', '', $namaOriginal));
            $tarifMap[$key] = (int)($item['harga'] ?? 0);
            $stats[$key] = 0;
            $chartData[$key] = [];
            $objekNames[$key] = $namaOriginal;
        }
        
        if (empty($stats)) {
            $stats = ['lainnya' => 0];
            $chartData = ['lainnya' => []];
            $objekNames = ['lainnya' => 'Lainnya'];
        }

        // 1. Ambil SEMUA data dari survei_harian (Struktur Berjenjang)
        $surveiHarianRaw = $this->database->getReference('survei_harian')->getValue() ?? [];
        
        $totalPendapatan = 0;
        $detailPendapatan = [];
        $groupedSurvei = []; // Untuk grafik mingguan

        foreach ($surveiHarianRaw as $idLokasi => $dataTanggal) {
            if (!is_array($dataTanggal)) continue;
            foreach ($dataTanggal as $tgl => $dataJam) {
                // Inisialisasi Grouped Survei untuk Tanggal Ini
                if (!isset($groupedSurvei[$tgl])) {
                    $groupedSurvei[$tgl] = [];
                    foreach ($stats as $k => $v) $groupedSurvei[$tgl][$k] = 0;
                }

                // Loop Jam (00-23)
                if (is_array($dataJam)) {
                    foreach ($dataJam as $hour => $dataPenugasan) {
                        // Loop Penugasan dalam jam tersebut
                        if (is_array($dataPenugasan)) {
                            foreach ($dataPenugasan as $idPenugasan => $item) {
                                if (is_array($item)) {
                                    foreach ($stats as $jenis => $v) {
                                        $jumlahUnit = (int)($item[$jenis] ?? 0);
                                        if ($jumlahUnit > 0) {
                                            // Akumulasi untuk Grafik Mingguan
                                            $groupedSurvei[$tgl][$jenis] += $jumlahUnit;

                                            // Akumulasi untuk Hari Ini (Statistik Utama & Tabel)
                                            if ($tgl == $hariIni) {
                                                $stats[$jenis] += $jumlahUnit;
                                                $harga = $tarifMap[$jenis] ?? 0;
                                                $pendapatanItem = $jumlahUnit * $harga;
                                                $totalPendapatan += $pendapatanItem;

                                                $keyDetail = $idLokasi . '_' . $jenis;
                                                if (!isset($detailPendapatan[$keyDetail])) {
                                                    $detailPendapatan[$keyDetail] = [
                                                        'objek' => $objekNames[$jenis] ?? ucfirst($jenis),
                                                        'id_lokasi' => $idLokasi,
                                                        'jumlah' => 0,
                                                        'nominal' => 0
                                                    ];
                                                }
                                                $detailPendapatan[$keyDetail]['jumlah'] += $jumlahUnit;
                                                $detailPendapatan[$keyDetail]['nominal'] += $pendapatanItem;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        foreach ($detailPendapatan as &$detail) {
            $idL = $detail['id_lokasi'];
            $detail['nama_lokasi'] = $lokasiMaster[$idL]['nama_lokasi'] ?? ($lokasiMaster[$idL]['alamat'] ?? 'Lokasi Tidak Dikenal');
        }

        $labelsMingguan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Makassar')->subDays($i);
            $dateString = $date->toDateString();
            $labelsMingguan[] = $date->translatedFormat('D'); 

            foreach($stats as $key => $val) {
                $count = 0;
                if (isset($groupedSurvei[$dateString][$key])) {
                    $count = $groupedSurvei[$dateString][$key];
                }
                $chartData[$key][] = $count;
            }
        }

        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        $unreadCount = 0;
        if (is_array($notifRaw)) {
            foreach ($notifRaw as $notif) {
                if (($notif['status'] ?? '') == 'unread') {
                    $unreadCount++;
                }
            }
        }

        return view('admin.dashboardadmin', [
            'totalPendapatan' => $totalPendapatan,
            'stats' => $stats,
            'objekNames' => $objekNames,
            'detailPendapatan' => $detailPendapatan,
            'labelsMingguan' => $labelsMingguan,
            'chartData' => $chartData,
            'unreadCount' => $unreadCount
        ]);
    }

    public function getNotifications()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        krsort($notifRaw); // Urutkan terbaru di atas
        return response()->json($notifRaw);
    }

    public function markNotificationsRead()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        foreach ($notifRaw as $key => $notif) {
            $this->database->getReference('notifikasi/' . $key . '/status')->set('read');
        }
        return response()->json(['success' => true]);
    }
}
