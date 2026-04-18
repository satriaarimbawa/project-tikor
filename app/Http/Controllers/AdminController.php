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

        $surveiRaw = $this->database->getReference('hasil_survei')->getValue() ?? [];
        $totalPendapatan = 0;
        $detailPendapatan = [];

        foreach ($surveiRaw as $item) {
            $createdAt = $item['created_at'] ?? '';
            if (empty($createdAt)) continue;

            $tglInput = Carbon::parse($createdAt)->toDateString();
            
            if ($tglInput == $hariIni) {
                $jenisRaw = $item['jenis_kendaraan'] ?? 'Lainnya';
                $jenis = strtolower(str_replace(' ', '', $jenisRaw));
                $harga = $tarifMap[$jenis] ?? 0;
                
                if (isset($stats[$jenis])) {
                    $stats[$jenis]++;
                } else {
                    if(!isset($stats['lainnya'])) $stats['lainnya'] = 0;
                    $stats['lainnya']++;
                }

                $totalPendapatan += $harga;

                $idLokasi = $item['id_lokasi'] ?? 'Unknown';
                $keyDetail = $idLokasi . '_' . $jenis;

                if (!isset($detailPendapatan[$keyDetail])) {
                    $detailPendapatan[$keyDetail] = [
                        'objek' => ucfirst($jenisRaw),
                        'id_lokasi' => $idLokasi,
                        'jumlah' => 0,
                        'nominal' => 0
                    ];
                }
                $detailPendapatan[$keyDetail]['jumlah']++;
                $detailPendapatan[$keyDetail]['nominal'] += $harga;
            }
        }

        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        foreach ($detailPendapatan as &$detail) {
            $idL = $detail['id_lokasi'];
            $detail['nama_lokasi'] = $lokasiMaster[$idL]['nama_lokasi'] ?? ($lokasiMaster[$idL]['alamat'] ?? 'Lokasi Tidak Dikenal');
        }

        $labelsMingguan = [];
        $groupedSurvei = [];
        foreach ($surveiRaw as $item) {
            $createdAt = $item['created_at'] ?? null;
            if ($createdAt) {
                $dateKey = Carbon::parse($createdAt)->toDateString();
                $jenis = strtolower(str_replace(' ', '', $item['jenis_kendaraan'] ?? ''));
                if (!isset($groupedSurvei[$dateKey])) {
                    $groupedSurvei[$dateKey] = [];
                    foreach($stats as $k => $v) $groupedSurvei[$dateKey][$k] = 0;
                }
                if (isset($groupedSurvei[$dateKey][$jenis])) {
                    $groupedSurvei[$dateKey][$jenis]++;
                }
            }
        }

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
        foreach ($notifRaw as $notif) {
            if (($notif['status'] ?? '') == 'unread') {
                $unreadCount++;
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
}
