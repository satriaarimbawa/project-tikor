<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanLokasiController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        $lokasiId = $request->input('lokasi_id');
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->subDays(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->format('Y-m-d'));

        $lokasiRef = $this->database->getReference('lokasi')->getValue() ?? [];
        $tarifRef = $this->database->getReference('objek_tarif')->getValue() ?? [];
        
        $mapTarif = [];
        $objekNames = [];
        $totals = [];
        foreach ($tarifRef as $t) {
            $namaOriginal = $t['nama'] ?? 'Lainnya';
            $key = strtolower(str_replace(' ', '', $namaOriginal));
            $mapTarif[$key] = $t['harga'] ?? 0;
            $objekNames[$key] = $namaOriginal;
            $totals[$key] = 0;
        }

        $summary = [];
        $grandTotalPenerimaan = 0;
        $grandTotalVolume = 0;

        if ($lokasiId) {
            $dataLokasiRaw = $this->database->getReference("survei_harian/{$lokasiId}")->getValue() ?? [];

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            foreach ($dataLokasiRaw as $tgl => $dataJam) {
                $tglCarbon = Carbon::parse($tgl, 'Asia/Makassar');
                
                if ($tglCarbon->between($start, $end)) {
                    if (!isset($summary[$tgl])) {
                        $summary[$tgl] = [
                            'tgl_display' => $tglCarbon->translatedFormat('d F Y'),
                            'details' => [],
                            'total_harian' => 0
                        ];
                    }

                    if (is_array($dataJam)) {
                        foreach ($dataJam as $hour => $dataPenugasan) {
                            if (is_array($dataPenugasan)) {
                                foreach ($dataPenugasan as $idPenugasan => $item) {
                                    foreach ($mapTarif as $jenisKey => $harga) {
                                        $vol = (int)($item[$jenisKey] ?? 0);
                                        if ($vol > 0) {
                                            $totals[$jenisKey] += $vol;

                                            if (!isset($summary[$tgl]['details'][$jenisKey])) {
                                                $summary[$tgl]['details'][$jenisKey] = [
                                                    'nama' => $objekNames[$jenisKey],
                                                    'tarif' => $harga,
                                                    'vol' => 0,
                                                    'total' => 0
                                                ];
                                            }

                                            $summary[$tgl]['details'][$jenisKey]['vol'] += $vol;
                                            $summary[$tgl]['details'][$jenisKey]['total'] += ($vol * $harga);
                                            $summary[$tgl]['total_harian'] += ($vol * $harga);
                                            
                                            $grandTotalPenerimaan += ($vol * $harga);
                                            $grandTotalVolume += $vol;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        krsort($summary);

        $labels = [];
        $values = [];
        foreach (array_reverse($summary) as $t => $data) {
            $labels[] = Carbon::parse($t)->format('d/m');
            $values[] = $data['total_harian'];
        }

        $volumeChartLabels = [];
        $volumeChartValues = [];
        foreach ($objekNames as $key => $nama) {
            $volumeChartLabels[] = $nama;
            $volumeChartValues[] = $totals[$key] ?? 0;
        }

        return view('admin.laporan_lokasi', [
            'daftarLokasi' => $lokasiRef,
            'lokasiId' => $lokasiId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'totals' => $totals,
            'objekNames' => $objekNames,
            'totalPenerimaan' => $grandTotalPenerimaan,
            'totalVolume' => $grandTotalVolume,
            'chartLabels' => $labels,
            'chartValues' => $values,
            'volumeChartLabels' => $volumeChartLabels,
            'volumeChartValues' => $volumeChartValues
        ]);
    }

    public function filter(Request $request)
    {
        return redirect()->route('laporan.lokasi', [
            'lokasi_id' => $request->lokasi_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->endOfMonth()->format('Y-m-d'));

        // 1. Tarik Data Firebase
        $lokasiRef = $this->database->getReference('lokasi')->getValue() ?? [];
        $tarifRef = $this->database->getReference('objek_tarif')->getValue() ?? [];

        // Mapping Objek Tarif
        $mapTarif = [];
        $objekNames = [];
        foreach ($tarifRef as $t) {
            $namaOriginal = $t['nama'] ?? 'Lainnya';
            $key = strtolower(str_replace(' ', '', $namaOriginal));
            $mapTarif[$key] = $t['harga'] ?? 0;
            $objekNames[$key] = $namaOriginal;
        }

        // Generate Array Tanggal
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $dates = [];
        $dataHarian = [];
        $gabungan = [];

        // Inisialisasi struktur harian kosong
        for($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->format('Y-m-d');
            $dates[] = $dateStr;
            
            // Struktur per lokasi
            foreach ($lokasiRef as $locId => $loc) {
                $dataHarian[$dateStr][$locId] = [
                    'hasil_uji_petik' => 0,
                    'volume' => [],
                    'penerimaan' => []
                ];
                foreach ($mapTarif as $key => $harga) {
                    $dataHarian[$dateStr][$locId]['volume'][$key] = 0;
                    $dataHarian[$dateStr][$locId]['penerimaan'][$key] = 0;
                }
            }

            // Struktur gabungan
            $gabungan[$dateStr] = [
                'hasil_uji_petik' => 0,
                'volume' => [],
                'penerimaan' => []
            ];
            foreach ($mapTarif as $key => $harga) {
                $gabungan[$dateStr]['volume'][$key] = 0;
                $gabungan[$dateStr]['penerimaan'][$key] = 0;
            }
        }

        // Isi Data dari survei_harian
        foreach ($lokasiRef as $locId => $loc) {
            $dataLokasiRaw = $this->database->getReference("survei_harian/{$locId}")->getValue() ?? [];
            foreach ($dataLokasiRaw as $tgl => $dataJam) {
                if (isset($dataHarian[$tgl][$locId])) {
                    if (is_array($dataJam)) {
                        foreach ($dataJam as $hour => $dataPenugasan) {
                            if (is_array($dataPenugasan)) {
                                foreach ($dataPenugasan as $idPenugasan => $item) {
                                    foreach ($mapTarif as $jenisKey => $harga) {
                                        $vol = (int)($item[$jenisKey] ?? 0);
                                        if ($vol > 0) {
                                            $penerimaan = $vol * $harga;
                                            
                                            // Akumulasi per lokasi
                                            $dataHarian[$tgl][$locId]['volume'][$jenisKey] += $vol;
                                            $dataHarian[$tgl][$locId]['penerimaan'][$jenisKey] += $penerimaan;
                                            $dataHarian[$tgl][$locId]['hasil_uji_petik'] += $penerimaan;
                                            
                                            // Akumulasi gabungan
                                            $gabungan[$tgl]['volume'][$jenisKey] += $vol;
                                            $gabungan[$tgl]['penerimaan'][$jenisKey] += $penerimaan;
                                            $gabungan[$tgl]['hasil_uji_petik'] += $penerimaan;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $pdf = Pdf::loadView('admin.laporan.pdf_uji_petik', [
            'dates' => $dates,
            'lokasiRef' => $lokasiRef,
            'objekNames' => $objekNames,
            'mapTarif' => $mapTarif,
            'dataHarian' => $dataHarian,
            'gabungan' => $gabungan
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Uji_Petik_'.Carbon::now()->format('Ymd_His').'.pdf');
    }
}
