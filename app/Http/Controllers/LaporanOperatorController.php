<?php

namespace App\Http\Controllers;

use App\Support\ObjekKunci;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

/**
 * Rekapitulasi hasil uji petik per operator.
 *
 * Bedanya dengan `LaporanLokasiController` adalah tingkat granularnya.
 * Laporan lokasi menjumlah rentang tanggal; laporan operator melihat
 * SATU lokasi pada SATU tanggal, lalu memecahnya per jam.
 *
 * PENTING: parameter `date` di sini adalah satu hari, bukan rentang.
 * Default-nya `Carbon::now()`, jadi halaman yang dibuka tanpa parameter
 * hanya menampilkan hari ini - bukan 7 hari terakhir seperti
 * `LaporanLokasiController::index()`. Jangan tertukar saat debugging.
 *
 * Menghitung volume selalu lewat `ObjekKunci`, bukan membaca
 * `objek_tarif.nama` langsung. Penjelasan lengkap ada di class
 * `App\Support\ObjekKunci`.
 *
 * Struktur data yang dibaca:
 *
 *   survei_harian/{id_lokasi}/{YYYY-MM-DD}/{HH}/{id_penugasan}/{jenis: jumlah}
 *
 * Field `user_id` di dalam record penugasan dipakai untuk memisahkan
 * rekap per petugas.
 */
class LaporanOperatorController extends Controller
{
    /**
     * Rentang jam yang ditampilkan pada grafik waktu.
     *
     * 00:00-05:00 di luar rentang karena tidak ada aktivitas operasional
     * di jam tersebut, dan menampilkannya membuat grafik sulit dibaca.
     */
    private const GRAFIK_JAM_MULAI = 6;

    private const GRAFIK_JAM_SELESAI = 22;

    /**
     * Koneksi Firebase Realtime Database.
     */
    protected Database $database;

    /**
     * @param Database $database Injeksi dari container Laravel.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Rekapitulasi versi HTML.
     *
     * Alur:
     *   1. Ambil master `lokasi`, `users`, dan `objek_tarif`.
     *   2. Bangun peta tarif yang di-key dengan KUNCI DATA.
     *   3. Telusuri 24 jam pada satu tanggal terpilih,_opsional filter
     *      satu user, lalu akumulasikan volume, penerimaan, dan rekap
     *      per jam.
     *
     * Jam tanpa data tidak masuk `$rekapitulasi`, sehingga tabel tidak
     * memunculkan baris kosong.
     *
     * @param Request $request Parameter:
     *   - `lokasi_id` (wajib untuk hasil berguna; kosong = semua data kosong).
     *   - `user_id`   (opsional, filter satu petugas).
     *   - `date`      (opsional, default hari ini).
     *
     * @return \Illuminate\View\View View `admin.laporan.lapOperator`.
     */
    public function lapOperator(Request $request)
    {
        $lokasiId = $request->input('lokasi_id');
        $userId = $request->input('user_id');
        $selectedDate = $request->input('date', Carbon::now('Asia/Makassar')->toDateString());

        // 1. Master data untuk filter di form.
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $userMaster = $this->database->getReference('users')->getValue() ?? [];
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];

        // Peta tarif di-key dengan kunci data. `nama` hanya untuk label.
        $mapTarif = [];
        $objekNames = [];
        $dataRingkasan = [];
        foreach (ObjekKunci::petaTarif($tarifRaw) as $key => $info) {
            $mapTarif[$key] = [
                'harga' => $info['harga'],
                'tarif_lama' => $info['tarif_lama'],
            ];
            $objekNames[$key] = $info['nama'];
            $dataRingkasan[$key] = 0;
        }

        // 2. Placeholder output.
        $rekapitulasi = [];
        $grafikWaktu = ['labels' => [], 'data' => []];
        $totalKeuangan = ['target' => 0, 'realisasi' => 0];

        // 3. Data survei untuk satu lokasi pada satu tanggal.
        if ($lokasiId) {
            $dataHarian = $this->database
                ->getReference("survei_harian/{$lokasiId}/{$selectedDate}")
                ->getValue() ?? [];

            for ($h = 0; $h <= 23; $h++) {
                $hourKey = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
                $labelJam = $hourKey . ':00';
                $jamTotal = 0;

                if (isset($dataHarian[$hourKey]) && is_array($dataHarian[$hourKey])) {
                    $detailsJam = [];
                    $totalPenerimaanJam = 0;

                    foreach ($dataHarian[$hourKey] as $idPenugasan => $item) {
                        $item = (array) $item;

                        // Filter operator bila dipilih.
                        if ($userId && ($item['user_id'] ?? '') != $userId) {
                            continue;
                        }

                        foreach ($mapTarif as $jenis => $tarifData) {
                            $harga = $tarifData['harga'];
                            $tarifLama = $tarifData['tarif_lama'];
                            $vol = ObjekKunci::volume($item, $jenis);
                            if ($vol <= 0) {
                                continue;
                            }

                            $penerimaan = $vol * $harga;

                            $dataRingkasan[$jenis] = ($dataRingkasan[$jenis] ?? 0) + $vol;
                            $jamTotal += $vol;
                            $totalPenerimaanJam += $penerimaan;
                            $totalKeuangan['realisasi'] += $penerimaan;

                            $detailsJam[] = [
                                'jenis' => $objekNames[$jenis],
                                'jumlah' => $vol,
                                'tarif' => $harga,
                                'tarif_lama' => $tarifLama,
                                'penerimaan' => $penerimaan,
                            ];
                        }
                    }

                    // Jam tanpa satu pun kendaraan tidak masuk tabel.
                    if ($detailsJam !== []) {
                        $rekapitulasi[] = [
                            'waktu' => $labelJam,
                            'details' => $detailsJam,
                            'total_penerimaan' => $totalPenerimaanJam,
                        ];
                    }
                }

                // Grafik waktu: seluruh jam tetap diplot (termasuk yang nol)
                // agar bentuk kurva tetap fair.
                if ($h >= self::GRAFIK_JAM_MULAI && $h <= self::GRAFIK_JAM_SELESAI) {
                    $grafikWaktu['labels'][] = $labelJam;
                    $grafikWaktu['data'][] = $jamTotal;
                }
            }

            // Target harian diambil dari master lokasi, bukan dari data survei.
            $totalKeuangan['target'] = (int) ($lokasiMaster[$lokasiId]['target_harian'] ?? 0);
        }

        // 4. Grafik volume per objek tarif.
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
                'data' => $volumeChartValues,
            ],
        ]);
    }

    /**
     * Unduh rekapitulasi operator format PDF (A4 portrait).
     *
     * Berbeda dengan `lapOperator()`, versi PDF mewajibkan `lokasi_id`
     * karena judul dokumen selalu menyebut nama lokasi.
     *
     * Perhitungan HARUS identik dengan versi HTML. Keduanya sengaja
     * memakai logika yang sama di file ini, Despite duplikasi, agar
     * tidak ada risiko salah satu berubah dan tidak ikut berubah.
     *
     * @param Request $request Parameter `lokasi_id` (wajib), `user_id`
     *                         (opsional), `date` (default hari ini).
     * @return \Symfony\Component\HttpFoundation\Response PDF sebagai
     *         unduhan, atau redirect dengan pesan error bila lokasi kosong.
     */
    public function downloadPdf(Request $request)
    {
        $lokasiId = $request->input('lokasi_id');
        $userId = $request->input('user_id');
        $selectedDate = $request->input('date', Carbon::now('Asia/Makassar')->toDateString());

        if (! $lokasiId) {
            return back()->with('error', 'Pilih lokasi terlebih dahulu.');
        }

        // 1. Master data.
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $userMaster = $this->database->getReference('users')->getValue() ?? [];
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];

        $mapTarif = [];
        $objekNames = [];
        $dataRingkasan = [];
        foreach (ObjekKunci::petaTarif($tarifRaw) as $key => $info) {
            $mapTarif[$key] = [
                'harga' => $info['harga'],
                'tarif_lama' => $info['tarif_lama'],
            ];
            $objekNames[$key] = $info['nama'];
            $dataRingkasan[$key] = 0;
        }

        $rekapitulasi = [];
        $totalKeuangan = [
            'target' => (int) ($lokasiMaster[$lokasiId]['target_harian'] ?? 0),
            'realisasi' => 0,
        ];

        // 2. Data survei.
        $dataHarian = $this->database
            ->getReference("survei_harian/{$lokasiId}/{$selectedDate}")
            ->getValue() ?? [];

        for ($h = 0; $h <= 23; $h++) {
            $hourKey = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
            $labelJam = $hourKey . ':00';

            if (! isset($dataHarian[$hourKey]) || ! is_array($dataHarian[$hourKey])) {
                continue;
            }

            $detailsJam = [];
            $totalPenerimaanJam = 0;

            foreach ($dataHarian[$hourKey] as $idPenugasan => $item) {
                $item = (array) $item;

                if ($userId && ($item['user_id'] ?? '') != $userId) {
                    continue;
                }

                foreach ($mapTarif as $jenis => $tarifData) {
                    $harga = $tarifData['harga'];
                    $tarifLama = $tarifData['tarif_lama'];
                    $vol = ObjekKunci::volume($item, $jenis);
                    if ($vol <= 0) {
                        continue;
                    }

                    $penerimaan = $vol * $harga;

                    $dataRingkasan[$jenis] += $vol;
                    $totalPenerimaanJam += $penerimaan;
                    $totalKeuangan['realisasi'] += $penerimaan;

                    $detailsJam[] = [
                        'jenis' => $objekNames[$jenis],
                        'jumlah' => $vol,
                        'tarif' => $harga,
                        'tarif_lama' => $tarifLama,
                        'penerimaan' => $penerimaan,
                    ];
                }
            }

            if ($detailsJam !== []) {
                $rekapitulasi[] = [
                    'waktu' => $labelJam,
                    'details' => $detailsJam,
                    'total_penerimaan' => $totalPenerimaanJam,
                ];
            }
        }

        $namaOperator = $userId ? ($userMaster[$userId]['username'] ?? 'Petugas') : 'Semua Petugas';
        $namaLokasi = $lokasiMaster[$lokasiId]['nama_lokasi'] ?? ($lokasiMaster[$lokasiId]['alamat'] ?? 'Lokasi');

        $pdf = Pdf::loadView('admin.laporan.pdf_operator', [
            'namaLokasi' => $namaLokasi,
            'namaOperator' => $namaOperator,
            'selectedDate' => Carbon::parse($selectedDate)->translatedFormat('d F Y'),
            'dataRingkasan' => $dataRingkasan,
            'objekNames' => $objekNames,
            'totalKedatangan' => array_sum($dataRingkasan),
            'keuangan' => $totalKeuangan,
            'rekapitulasi' => $rekapitulasi,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Operator_' . Carbon::now()->format('Ymd_His') . '.pdf');
    }
}
