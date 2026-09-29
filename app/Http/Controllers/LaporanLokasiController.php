<?php

namespace App\Http\Controllers;

use App\Support\ObjekKunci;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

/**
 * Laporan uji petik berdasarkan lokasi.
 *
 * Menampilkan dua bentuk laporan dari sumber data yang sama
 * (`survei_harian`):
 *
 *   - `index()`      -> HTML interaktif untuk layar
 *   - `downloadPdf()`-> PDF siap cetak (A4 landscape)
 *
 * Keduanya wajib menghasilkan angka IDENTIK. Tester harus bisa
 * mencocokkan baris "Gabungan" di PDF dengan kartu total di HTML.
 *
 * Menghitung volume selalu lewat `ObjekKunci`, bukan dengan membaca
 * `objek_tarif.nama` langsung. Alasan dan dampaknya dijelaskan
 * lengkap di class `App\Support\ObjekKunci`.
 *
 * Struktur data yang dibaca:
 *
 *   survei_harian/{id_lokasi}/{YYYY-MM-DD}/{HH}/{id_penugasan}/{jenis: jumlah}
 */
class LaporanLokasiController extends Controller
{
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
     * Laporan uji petik versi HTML.
     *
     * Alur:
     *   1. Ambil master `lokasi` dan `objek_tarif`.
     *   2. Bangun peta tarif yang di-key dengan KUNCI DATA, bukan nama.
     *   3. Telusuri `survei_harian` dalam rentang tanggal yang diminta dan
     *      akumulasikan volume per kunci, per hari, dan grand total.
     *   4. Laporkan kunci yang ada di data tapi tidak dimiliki objek tarif
     *      mana pun lewat `takTerpetakan`, agar tidak hilang diam-diam.
     *
     * Catatan: record di luar rentang tanggal dilewati sepenuhnya. Angka
     *     di `volumePerKunci` hanya berasal dari tanggal yang masuk
     *     rentang, jadi peringatan `takTerpetakan` juga ikut ter-filter
     *     rentang - dan itu disengaja, agar peringatan terasa relevan dengan
     *     periode yang sedang dilihat.
     *
     * @param Request $request Parameter:
     *   - `lokasi_id` (opsional). Kosong = gabungan semua lokasi.
     *   - `start_date` (opsional, default 7 hari terakhir).
     *   - `end_date`   (opsional, default hari ini).
     *
     * @return \Illuminate\View\View View `admin.laporan_lokasi`.
     */
    public function index(Request $request)
    {
        $lokasiId = $request->input('lokasi_id');
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->subDays(6)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->format('Y-m-d'));

        $lokasiRef = $this->database->getReference('lokasi')->getValue() ?? [];
        $tarifRef = $this->database->getReference('objek_tarif')->getValue() ?? [];

        // Peta tarif di-key dengan kunci data. `nama` hanya untuk ditampilkan,
        // tidak pernah dipakai untuk mencocokkan record survei.
        $mapTarif = [];
        $objekNames = [];
        $totals = [];
        foreach (ObjekKunci::petaTarif($tarifRef) as $key => $info) {
            $mapTarif[$key] = $info['harga'];
            $objekNames[$key] = $info['nama'];
            $totals[$key] = 0;
        }

        // Volume per kunci dihitung dari SELURUH data survei - termasuk kunci
        // yang tidak punya objek tarif. Angka ini hanya dipakai untuk
        // peringatan `takTerpetakan`, TIDAK masuk grand total.
        $volumePerKunci = [];

        $summary = [];
        $grandTotalPenerimaan = 0;
        $grandTotalVolume = 0;

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($lokasiId) {
            $surveiRaw = [$lokasiId => $this->database->getReference("survei_harian/{$lokasiId}")->getValue() ?? []];
        } else {
            // Tidak pilih lokasi = gabungan semua lokasi.
            $surveiRaw = $this->database->getReference('survei_harian')->getValue() ?? [];
        }

        foreach ($surveiRaw as $locId => $datesData) {
            if (! is_array($datesData)) {
                continue;
            }

            foreach ($datesData as $tgl => $dataJam) {
                $tglCarbon = Carbon::parse($tgl, 'Asia/Makassar');

                if (! $tglCarbon->between($start, $end)) {
                    continue;
                }
                if (! is_array($dataJam)) {
                    continue;
                }

                $summary[$tgl] ??= [
                    'tgl_display' => $tglCarbon->translatedFormat('d F Y'),
                    'details' => [],
                    'total_harian' => 0,
                ];

                foreach ($dataJam as $hour => $dataPenugasan) {
                    if (! is_array($dataPenugasan)) {
                        continue;
                    }

                    foreach ($dataPenugasan as $idPenugasan => $item) {
                        $item = (array) $item;

                        // Rekam semua kunci kendaraan yang benar-benar ada di
                        // record ini. Field non-kendaraan (`total_survei`,
                        // `user_id`) otomatis dilewati oleh `kunciSurvei()`.
                        foreach (ObjekKunci::kunciSurvei($item) as $fk => $ignored) {
                            $volumePerKunci[$fk] = ($volumePerKunci[$fk] ?? 0) + (int) $item[$fk];
                        }

                        foreach ($mapTarif as $jenisKey => $harga) {
                            $vol = ObjekKunci::volume($item, $jenisKey);
                            if ($vol <= 0) {
                                continue;
                            }

                            $totals[$jenisKey] += $vol;

                            $summary[$tgl]['details'][$jenisKey] ??= [
                                'nama' => $objekNames[$jenisKey],
                                'tarif' => $harga,
                                'vol' => 0,
                                'total' => 0,
                            ];

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

        // Tanggal terbaru di atas.
        krsort($summary);

        // Kunci kendaraan yang ada di data survei tapi tidak dimiliki objek
        // tarif mana pun. Ditempatkan di UI agar tidak hilang tanpa jejak.
        $takTerpetakan = ObjekKunci::takTerpetakan($volumePerKunci, $tarifRef);

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
            'volumeChartValues' => $volumeChartValues,
            'takTerpetakan' => $takTerpetakan,
        ]);
    }

    /**
     * Endpoint pembantu untuk form filter di halaman laporan.
     *
     * Form disubmit ke sini, lalu controller mengalihkan ke `index()` dengan
     * query string yang sudah bersih. Tujuannya agar URL hasil filter selalu
     * punya bentuk sama dan bisa disalin/dibagikan.
     *
     * @param Request $request Parameter `lokasi_id`, `start_date`, `end_date`.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function filter(Request $request)
    {
        return redirect()->route('laporan.lokasi', [
            'lokasi_id' => $request->lokasi_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    /**
     * Unduh laporan uji petik format PDF (A4 landscape).
     *
     * Menyusun tabel matriks: baris = lokasi, kolom = tanggal, dan setiap
     * sel berisi volume + penerimaan per objek tarif.
     *
     * PERINGATAN - dua hal di sini mudah regresi:
     *
     *   1. Objek tarif dicocokkan lewat `ObjekKunci`, bukan `nama`.
     *   2. Lokasi yatim (punya data survei tapi tidak terdaftar di master
     *      `lokasi`) tidak bisa dicetak sebagai kolom karena tidak ada
     *      nama, TAPI volumenya tetap harus ikut baris "Gabungan".
     *      5 lokasi / 819 unit / Rp 1.856.000 pernah hilang hanya di PDF
     *      karena `continue` dilakukan terlalu awal.
     *
     * Kontrak hasil: baris "Gabungan" pada PDF wajib sama dengan
     * `totalVolume` dan `totalPenerimaan` yang dirender di HTML `index()`.
     *
     * @param Request $request Parameter `lokasi_id` (opsional), `start_date`,
     *                         `end_date`. Default = bulan berjalan.
     * @return \Symfony\Component\HttpFoundation\Response PDF sebagai unduhan,
     *         atau redirect dengan pesan error bila tidak ada data.
     */
    public function downloadPdf(Request $request)
    {
        $lokasiIdSelected = $request->input('lokasi_id');
        $startDate = $request->input('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now('Asia/Makassar')->endOfMonth()->format('Y-m-d'));

        // 1. Master data.
        $lokasiRef = $this->database->getReference('lokasi')->getValue() ?? [];
        $tarifRef = $this->database->getReference('objek_tarif')->getValue() ?? [];

        // Peta tarif di-key dengan kunci data.
        $mapTarif = [];
        $objekNames = [];
        foreach (ObjekKunci::petaTarif($tarifRef) as $key => $info) {
            $mapTarif[$key] = $info['harga'];
            $objekNames[$key] = $info['nama'];
        }

        // 2. Daftar tanggal yang akan jadi kolom.
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        // 3. Lokasi yang akan punya kolom di tabel.
        $lokasiToProcess = [];
        if ($lokasiIdSelected) {
            if (! isset($lokasiRef[$lokasiIdSelected])) {
                return back()->with('error', 'Lokasi tidak ditemukan.');
            }
            $lokasiToProcess[$lokasiIdSelected] = $lokasiRef[$lokasiIdSelected];
        } else {
            $lokasiToProcess = $lokasiRef;
        }

        $dataHarian = [];
        $gabungan = [];

        // Inisialisasi nol untuk setiap tanggal, lokasi, dan objek tarif, agar
        // sel yang tidak ada datanya tetap tercetak sebagai 0.
        foreach ($dates as $dateStr) {
            $gabungan[$dateStr] = [
                'hasil_uji_petik' => 0,
                'volume' => array_fill_keys(array_keys($mapTarif), 0),
                'penerimaan' => array_fill_keys(array_keys($mapTarif), 0),
            ];

            foreach ($lokasiToProcess as $locId => $loc) {
                $dataHarian[$dateStr][$locId] = [
                    'hasil_uji_petik' => 0,
                    'volume' => array_fill_keys(array_keys($mapTarif), 0),
                    'penerimaan' => array_fill_keys(array_keys($mapTarif), 0),
                ];
            }
        }

        // 4. Data survei.
        if ($lokasiIdSelected) {
            $surveiRaw = [$lokasiIdSelected => $this->database->getReference("survei_harian/{$lokasiIdSelected}")->getValue() ?? []];
        } else {
            $surveiRaw = $this->database->getReference('survei_harian')->getValue() ?? [];
        }

        foreach ($surveiRaw as $locId => $datesData) {
            if (! is_array($datesData)) {
                continue;
            }

            // Lokasi yatim: punya data survei tapi tidak terdaftar di master
            // `lokasi`. Kolomnya tidak bisa dicetak karena tidak ada nama,
            // tapi volumenya HARUS ikut hitungan "Gabungan" supaya total PDF
            // sama dengan laporan HTML. Sebelumnya baris di bawah ini langsung
            // `continue`, sehingga 5 lokasi / 819 unit / Rp 1.856.000 lenyap
            // diam-diam hanya di PDF.
            $punyaKolom = isset($lokasiToProcess[$locId]);

            foreach ($datesData as $tgl => $dataJam) {
                if (! isset($gabungan[$tgl])) {
                    continue;
                }
                if ($punyaKolom && ! isset($dataHarian[$tgl][$locId])) {
                    continue;
                }
                if (! is_array($dataJam)) {
                    continue;
                }

                foreach ($dataJam as $hour => $dataPenugasan) {
                    if (! is_array($dataPenugasan)) {
                        continue;
                    }

                    foreach ($dataPenugasan as $idPenugasan => $item) {
                        $item = (array) $item;

                        foreach ($mapTarif as $jenisKey => $harga) {
                            $vol = ObjekKunci::volume($item, $jenisKey);
                            if ($vol <= 0) {
                                continue;
                            }

                            $penerimaan = $vol * $harga;

                            if ($punyaKolom) {
                                $dataHarian[$tgl][$locId]['volume'][$jenisKey] += $vol;
                                $dataHarian[$tgl][$locId]['penerimaan'][$jenisKey] += $penerimaan;
                                $dataHarian[$tgl][$locId]['hasil_uji_petik'] += $penerimaan;
                            }

                            $gabungan[$tgl]['volume'][$jenisKey] += $vol;
                            $gabungan[$tgl]['penerimaan'][$jenisKey] += $penerimaan;
                            $gabungan[$tgl]['hasil_uji_petik'] += $penerimaan;
                        }
                    }
                }
            }
        }

        // 5. Saat menampilkan semua lokasi, buang lokasi yang nol total supaya
        //    PDF tidak dipenuhi halaman kosong.
        if (! $lokasiIdSelected) {
            foreach ($lokasiToProcess as $locId => $loc) {
                $hasData = false;
                foreach ($dates as $date) {
                    if ($dataHarian[$date][$locId]['hasil_uji_petik'] > 0) {
                        $hasData = true;
                        break;
                    }
                }
                if (! $hasData) {
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
            'gabungan' => $gabungan,
        ])->setPaper('a4', 'landscape');

        $labelFile = $lokasiIdSelected
            ? str_replace(' ', '_', $lokasiRef[$lokasiIdSelected]['nama_lokasi'] ?? 'Lokasi')
            : 'Gabungan_Semua';

        return $pdf->download('Laporan_Uji_Petik_' . $labelFile . '_' . $startDate . '.pdf');
    }
}
