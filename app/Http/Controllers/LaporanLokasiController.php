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

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($lokasiId) {
            $surveiRaw = [$lokasiId => $this->database->getReference("survei_harian/{$lokasiId}")->getValue() ?? []];
        } else {
            // Jika tidak pilih lokasi, ambil semua (Gabungan)
            $surveiRaw = $this->database->getReference("survei_harian")->getValue() ?? [];
        }

        foreach ($surveiRaw as $locId => $datesData) {
            if (!is_array($datesData)) continue;

            foreach ($datesData as $tgl => $dataJam) {
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
        $lokasiIdSelected = $request->input('lokasi_id');
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->endOfMonth()->format('Y-m-d'));

        // 1. Tarik Data Firebase Master
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
        for($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        // Filter lokasi yang akan diproses
        $lokasiToProcess = [];
        if ($lokasiIdSelected) {
            if (isset($lokasiRef[$lokasiIdSelected])) {
                $lokasiToProcess[$lokasiIdSelected] = $lokasiRef[$lokasiIdSelected];
            } else {
                return back()->with('error', 'Lokasi tidak ditemukan.');
            }
        } else {
            // Jika tidak pilih lokasi, ambil semua yang aktif atau punya data (disini ambil semua master lokasi)
            $lokasiToProcess = $lokasiRef;
        }

        $dataHarian = [];
        $gabungan = [];

        // Inisialisasi struktur
        foreach ($dates as $dateStr) {
            $gabungan[$dateStr] = [
                'hasil_uji_petik' => 0,
                'volume' => array_fill_keys(array_keys($mapTarif), 0),
                'penerimaan' => array_fill_keys(array_keys($mapTarif), 0)
            ];

            foreach ($lokasiToProcess as $locId => $loc) {
                $dataHarian[$dateStr][$locId] = [
                    'hasil_uji_petik' => 0,
                    'volume' => array_fill_keys(array_keys($mapTarif), 0),
                    'penerimaan' => array_fill_keys(array_keys($mapTarif), 0)
                ];
            }
        }

        // 3. Tarik Data Survei dari Firebase
        if ($lokasiIdSelected) {
            $surveiRaw = [$lokasiIdSelected => $this->database->getReference("survei_harian/{$lokasiIdSelected}")->getValue() ?? []];
        } else {
            $surveiRaw = $this->database->getReference("survei_harian")->getValue() ?? [];
        }

        foreach ($surveiRaw as $locId => $datesData) {
            if (!isset($lokasiToProcess[$locId])) continue;
            if (!is_array($datesData)) continue;
            
            foreach ($datesData as $tgl => $dataJam) {
                if (isset($dataHarian[$tgl][$locId])) {
                    if (is_array($dataJam)) {
                        foreach ($dataJam as $hour => $dataPenugasan) {
                            if (is_array($dataPenugasan)) {
                                foreach ($dataPenugasan as $idPenugasan => $item) {
                                    foreach ($mapTarif as $jenisKey => $harga) {
                                        $vol = (int)($item[$jenisKey] ?? 0);
                                        if ($vol > 0) {
                                            $penerimaan = $vol * $harga;
                                            
                                            $dataHarian[$tgl][$locId]['volume'][$jenisKey] += $vol;
                                            $dataHarian[$tgl][$locId]['penerimaan'][$jenisKey] += $penerimaan;
                                            $dataHarian[$tgl][$locId]['hasil_uji_petik'] += $penerimaan;
                                            
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

        // Optional: Jika "Semua Lokasi", mungkin kita ingin membuang lokasi yang tidak ada datanya sama sekali agar laporan tidak terlalu tebal dengan halaman kosong
        if (!$lokasiIdSelected) {
            foreach ($lokasiToProcess as $locId => $loc) {
                $hasData = false;
                foreach ($dates as $date) {
                    if ($dataHarian[$date][$locId]['hasil_uji_petik'] > 0) {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) {
                    unset($lokasiToProcess[$locId]);
                }
            }
        }

        if (empty($lokasiToProcess)) {
            return back()->with('error', 'Tidak ada data uji petik untuk rentang tanggal tersebut.');
        }

        $pdf = Pdf::loadView('admin.laporan.pdf_uji_petik', [
            'dates' => $dates,
            'lokasiRef' => $lokasiToProcess,
            'objekNames' => $objekNames,
            'mapTarif' => $mapTarif,
            'dataHarian' => $dataHarian,
            'gabungan' => $gabungan
        ])->setPaper('a4', 'landscape');

        $labelFile = $lokasiIdSelected ? str_replace(' ', '_', $lokasiRef[$lokasiIdSelected]['nama_lokasi'] ?? 'Lokasi') : 'Gabungan_Semua';
        $namaFile = 'Laporan_Uji_Petik_' . $labelFile . '_' . $startDate . '.pdf';

        return $pdf->download($namaFile);
    }
}
