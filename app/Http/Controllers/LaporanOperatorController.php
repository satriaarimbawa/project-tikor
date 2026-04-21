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
        foreach ($tarifRaw as $t) {
            $key = strtolower(str_replace(' ', '', $t['nama'] ?? ''));
            $mapTarif[$key] = $t['harga'] ?? 0;
        }

        // 2. Inisialisasi Data Output
        $dataRingkasan = ['motor' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];
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
                
                if (isset($dataHarian[$hourKey])) {
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
                                    'jenis' => ucfirst($jenis),
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

                // Untuk Grafik Waktu (hanya jam kerja atau semua)
                if ($h >= 6 && $h <= 22) {
                    $grafikWaktu['labels'][] = $labelJam;
                    $grafikWaktu['data'][] = $jamTotal;
                }
            }

            // Ambil Target Harian dari Lokasi
            $target = $lokasiMaster[$lokasiId]['target_harian'] ?? 0;
            $totalKeuangan['target'] = (int)$target;
        }

        return view('admin.laporan.lapOperator', [
            'lokasiMaster' => $lokasiMaster,
            'userMaster' => $userMaster,
            'lokasiId' => $lokasiId,
            'userId' => $userId,
            'selectedDate' => $selectedDate,
            'dataRingkasan' => $dataRingkasan,
            'totalKedatangan' => array_sum($dataRingkasan),
            'keuangan' => $totalKeuangan,
            'rekapitulasi' => $rekapitulasi,
            'grafikWaktu' => $grafikWaktu,
            'grafikVolume' => [
                'labels' => ['Motor', 'Mini Bus', 'Bus', 'Truk'],
                'data' => [$dataRingkasan['motor'], $dataRingkasan['minibus'], $dataRingkasan['bus'], $dataRingkasan['truk']]
            ]
        ]);
    }
}