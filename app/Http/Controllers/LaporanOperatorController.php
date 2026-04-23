<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class LaporanOperatorController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function lapOperator(Request $request)
    {
        $lokasiId = $request->input('lokasi_id');
        $userId = $request->input('user_id');
        $selectedDate = $request->input('date', Carbon::now('Asia/Makassar')->toDateString());

        // 1. Data Master untuk Filter
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $userMaster = $this->database->getReference('users')->getValue() ?? [];
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        
        $mapTarif = [];
        $objekNames = [];
        $dataRingkasan = []; // Dinamis

        foreach ($tarifRaw as $t) {
            $namaOriginal = $t['nama'] ?? 'Lainnya';
            $key = strtolower(str_replace(' ', '', $namaOriginal));
            $mapTarif[$key] = $t['harga'] ?? 0;
            $objekNames[$key] = $namaOriginal;
            $dataRingkasan[$key] = 0; // Inisialisasi awal 0
        }

        // 2. Inisialisasi Data Output Tambahan
        $rekapitulasi = [];
        $grafikWaktu = ['labels' => [], 'data' => []];
        $totalKeuangan = ['target' => 0, 'realisasi' => 0];

        // 3. Tarik Data dari survei_harian
        if ($lokasiId) {
            $dataHarian = $this->database->getReference("survei_harian/{$lokasiId}/{$selectedDate}")->getValue() ?? [];

            // Loop Jam (00-23)
            for ($h = 0; $h <= 23; $h++) {
                $hourKey = str_pad($h, 2, '0', STR_PAD_LEFT);
                $labelJam = $hourKey . ':00';
                $jamTotal = 0;
                
                if (isset($dataHarian[$hourKey]) && is_array($dataHarian[$hourKey])) {
                    $detailsJam = [];
                    $totalPenerimaanJam = 0;

                    foreach ($dataHarian[$hourKey] as $idPenugasan => $item) {
                        // Filter User jika dipilih
                        if ($userId && ($item['user_id'] ?? '') != $userId) continue;

                        foreach ($mapTarif as $jenis => $harga) {
                            $vol = (int)($item[$jenis] ?? 0);
                            if ($vol > 0) {
                                $dataRingkasan[$jenis] = ($dataRingkasan[$jenis] ?? 0) + $vol;
                                $jamTotal += $vol;
                                
                                $penerimaan = $vol * $harga;
                                $totalPenerimaanJam += $penerimaan;
                                $totalKeuangan['realisasi'] += $penerimaan;

                                $detailsJam[] = [
                                    'jenis' => $objekNames[$jenis],
                                    'jumlah' => $vol,
                                    'tarif' => $harga,
                                    'penerimaan' => $penerimaan
                                ];
                            }
                        }
                    }

                    if (!empty($detailsJam)) {
                        $rekapitulasi[] = [
                            'waktu' => $labelJam,
                            'details' => $detailsJam,
                            'total_penerimaan' => $totalPenerimaanJam
                        ];
                    }
                }

                // Untuk Grafik Waktu
                if ($h >= 6 && $h <= 22) {
                    $grafikWaktu['labels'][] = $labelJam;
                    $grafikWaktu['data'][] = $jamTotal;
                }
            }
        }

        // 6. Siapkan Data Grafik Volume (Dinamis)
        $volumeChartLabels = [];
        $volumeChartValues = [];
        foreach ($objekNames as $key => $nama) {
            $volumeChartLabels[] = $nama;
            $volumeChartValues[] = $dataRingkasan[$key] ?? 0;
        }

        return view('admin.laporan.lapOperator', [
            'lokasiMaster' => $lokasiMaster,
            'userMaster' => $userMaster,
            'lokasiId' => $lokasiId,
            'userId' => $userId,
            'selectedDate' => $selectedDate,
            'dataRingkasan' => $dataRingkasan,
            'objekNames' => $objekNames,
            'totalKedatangan' => array_sum($dataRingkasan),
            'keuangan' => $totalKeuangan,
            'rekapitulasi' => $rekapitulasi,
            'grafikWaktu' => $grafikWaktu,
            'grafikVolume' => [
                'labels' => $volumeChartLabels,
                'data' => $volumeChartValues
            ]
        ]);
    }
}