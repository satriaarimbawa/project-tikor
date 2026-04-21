<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class LaporanLokasiController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        // Langsung menggunakan Firebase Database Contract
        $this->database = $database;
    }

    public function index(Request $request)
    {
        // 1. Ambil Parameter Filter (Firebase-friendly)
        $lokasiId = $request->input('lokasi_id');
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->subDays(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->format('Y-m-d'));

        // 2. Tarik Seluruh Master Data dari Firebase (No SQL)
        $lokasiRef = $this->database->getReference('lokasi')->getValue() ?? [];
        $tarifRef = $this->database->getReference('objek_tarif')->getValue() ?? [];
        
        // Map Tarif ke Array agar pencarian cepat (O(1))
        $mapTarif = [];
        foreach ($tarifRef as $t) {
            $key = strtolower(str_replace(' ', '', $t['nama'] ?? ''));
            $mapTarif[$key] = $t['harga'] ?? 0;
        }

        // 3. Tarik Data dari survei_harian (Struktur Berjenjang)
        $summary = [];
        $totals = ['motor' => 0, 'mobil' => 0, 'minibus' => 0, 'bus' => 0, 'truk' => 0];
        $grandTotalPenerimaan = 0;
        $grandTotalVolume = 0;

        if ($lokasiId) {
            $dataLokasiRaw = $this->database->getReference("survei_harian/{$lokasiId}")->getValue() ?? [];

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            foreach ($dataLokasiRaw as $tgl => $dataJam) {
                $tglCarbon = Carbon::parse($tgl, 'Asia/Makassar');
                
                // Filter Rentang Tanggal
                if ($tglCarbon->between($start, $end)) {
                    
                    if (!isset($summary[$tgl])) {
                        $summary[$tgl] = [
                            'tgl_display' => $tglCarbon->translatedFormat('d F Y'),
                            'details' => [],
                            'total_harian' => 0
                        ];
                    }

                    // Loop Jam
                    foreach ($dataJam as $hour => $dataPenugasan) {
                        // Loop Penugasan
                        foreach ($dataPenugasan as $idPenugasan => $item) {
                            
                            foreach ($mapTarif as $jenisKey => $harga) {
                                $vol = (int)($item[$jenisKey] ?? 0);
                                if ($vol > 0) {
                                    // Klasifikasi untuk Totals di Atas
                                    if (str_contains($jenisKey, 'motor')) $totals['motor'] += $vol;
                                    elseif (str_contains($jenisKey, 'bus')) $totals['bus'] += $vol;
                                    elseif (str_contains($jenisKey, 'truk')) $totals['truk'] += $vol;
                                    else $totals['minibus'] += $vol;

                                    // Detail Tabel
                                    if (!isset($summary[$tgl]['details'][$jenisKey])) {
                                        $summary[$tgl]['details'][$jenisKey] = [
                                            'nama' => ucfirst($jenisKey),
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

        // Urutkan Tanggal Terbaru di Atas
        krsort($summary);

        // 5. Siapkan Data Grafik (Naik)
        $labels = [];
        $values = [];
        foreach (array_reverse($summary) as $t => $data) {
            $labels[] = Carbon::parse($t)->format('d/m');
            $values[] = $data['total_harian'];
        }

        return view('admin.laporan_lokasi', [
            'daftarLokasi' => $lokasiRef,
            'lokasiId' => $lokasiId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'totals' => $totals,
            'totalPenerimaan' => $grandTotalPenerimaan,
            'totalVolume' => $grandTotalVolume,
            'chartLabels' => $labels,
            'chartValues' => $values
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

    public function downloadPdf()
    {
        // Placeholder untuk fitur selanjutnya
        return back()->with('error', 'Fitur PDF dalam pengembangan.');
    }
}
