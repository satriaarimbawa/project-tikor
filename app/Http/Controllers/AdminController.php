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

        // 1. Ambil Data Tarif
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap = [];
        foreach ($tarifRaw as $item) {
            $key = strtolower(str_replace(' ', '', $item['nama'] ?? ''));
            $tarifMap[$key] = (int)($item['harga'] ?? 0);
        }

        // 2. Ambil Hasil Survei
        $surveiRaw = $this->database->getReference('hasil_survei')->getValue() ?? [];
        
        $totalPendapatan = 0;
        $stats = [
            'motor' => 0,
            'bus' => 0,
            'minibus' => 0,
            'truk' => 0,
            'lainnya' => 0
        ];

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

        // 3. Ambil Nama Lokasi
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        foreach ($detailPendapatan as &$detail) {
            $idL = $detail['id_lokasi'];
            $detail['nama_lokasi'] = $lokasiMaster[$idL]['nama_lokasi'] ?? ($lokasiMaster[$idL]['alamat'] ?? 'Lokasi Tidak Dikenal');
        }

        // 4. Logika Grafik Mingguan (6 Hari Terakhir)
        $labelsMingguan = [];
        $chartData = [
            'motor' => [],
            'bus' => [],
            'minibus' => [],
            'truk' => []
        ];

        // Kelompokkan data survei berdasarkan tanggal untuk mempercepat proses
        $groupedSurvei = [];
        foreach ($surveiRaw as $item) {
            $createdAt = $item['created_at'] ?? null;
            if ($createdAt) {
                $dateKey = Carbon::parse($createdAt)->toDateString();
                $jenis = strtolower(str_replace(' ', '', $item['jenis_kendaraan'] ?? ''));
                if (!isset($groupedSurvei[$dateKey])) {
                    $groupedSurvei[$dateKey] = ['motor' => 0, 'bus' => 0, 'minibus' => 0, 'truk' => 0];
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

            $countsForDay = $groupedSurvei[$dateString] ?? ['motor' => 0, 'bus' => 0, 'minibus' => 0, 'truk' => 0];
            
            $chartData['motor'][] = $countsForDay['motor'];
            $chartData['bus'][] = $countsForDay['bus'];
            $chartData['minibus'][] = $countsForDay['minibus'];
            $chartData['truk'][] = $countsForDay['truk'];
        }

        // 5. Ambil Jumlah Pesan Masuk (Unread)
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
            'detailPendapatan' => $detailPendapatan,
            'labelsMingguan' => $labelsMingguan,
            'chartData' => $chartData,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}