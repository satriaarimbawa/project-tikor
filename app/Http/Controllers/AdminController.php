<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use App\Services\PenugasanStatusService;
use App\Support\ObjekKunci;

/**
 * Dashboard utama admin.
 *
 * Menghitung pendapatan dan rekap survei SELURUH lokasi dari
 * `survei_harian`, lalu menyajikannya sebagai kartu angka, grafik, dan tabel.
 *
 * Aturan pencocokan objek: tarif dicocokkan lewat `ObjekKunci`, bukan lewat
 * `objek_tarif.nama`. Kalau ikut memakai `nama`, me-rename objek di master
 * akan membuat seluruh data historis objek itu lenyap dari dashboard.
 * Rinciannya ada di `App\Support\ObjekKunci`.
 */
class AdminController extends Controller
{
    /** Koneksi Firebase Realtime Database. */
    protected $database;

    /** Penulis otomatis field status penugasan. */
    protected $penugasanStatus;

    /**
     * @param Database $database Injeksi dari container Laravel.
     * @param PenugasanStatusService $penugasanStatus Injeksi dari container Laravel.
     */
    public function __construct(Database $database, PenugasanStatusService $penugasanStatus)
    {
        $this->database = $database;
        $this->penugasanStatus = $penugasanStatus;
    }

    /**
     * Form login admin.
     *
     * @return \Illuminate\View\View
     */
    public function loginadmin()
    {
        return view('login.logadmin');
    }

    /**
     * Isi dashboard admin.
     *
     * Tahapan:
     *   1. Bersihkan sesi penugasan lama yang sudah kedaluwarsa.
     *   2. Bangun peta tarif dari master `objek_tarif`, di-key dengan
     *      KUNCI DATA, plus peta balik sub-key -> parent-key untuk objek
     *      yang melayani lebih dari satu kunci (mis. "minibus,pick-up").
     *   3. Telusuri seluruh `survei_harian` dan akumulasikan pendapatan,
     *      jumlah per tanggal, dan total per jenis kendaraan.
     *   4. Susun data grafik dan notifikasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Carbon::setLocale('id');
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $hariIni = $waktuSekarang->toDateString();

        // --- AUTOMATIC CLEANUP: Deactivate expired assignments ---
        // Logikanya ada di App\Services\PenugasanStatusService, sama dengan
        // yang dipakai command penjadwal `penugasan:nonaktifkan`. Dulu blok
        // ini ditulis ulang sendiri sehingga bisa berbeda aturan dengan
        // command, dan sering gagal diam-diam karena `waktu_selesai` null
        // tidak pernah dicek sebelum di-parse.
        try {
            $this->penugasanStatus->nonaktifkanKedaluwarsa($waktuSekarang);
        } catch (\Throwable $e) {
            // Abaikan error cleanup agar dashboard tetap tampil jika Firebase bermasalah sebentar
        }
        // --- END CLEANUP ---

        // 1. Ambil Master Tarif & Inisialisasi Kunci Valid
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap   = [];
        $objekNames = [];
        $validKeys  = []; // parent keys, mis: "motor", "minibus,pick-up", "truk"
        $subKeyMap  = []; // reverse map: sub-key → parent-key
                          // mis: "minibus" → "minibus,pick-up", "pick-up" → "minibus,pick-up"

        foreach ($tarifRaw as $itemId => $item) {
            if (!is_array($item)) continue;

            $namaOriginal = $item['nama'] ?? 'Lainnya';
            $harga        = (int)($item['harga'] ?? 0);

            // Parent key TIDAK lagi turunan dari `nama`. `nama` cuma label
            // tampilan yang bebas diganti admin; kunci datanya permanen
            // (lihat App\Support\ObjekKunci). Inilah akar bug data historis
            // lenyap saat admin me-rename objek.
            $parentKey = ObjekKunci::untuk((string)$itemId, $item);

            if (!in_array($parentKey, $validKeys)) {
                $tarifMap[$parentKey]   = $harga;
                $objekNames[$parentKey] = $namaOriginal;
                $validKeys[]            = $parentKey;
            }

            // Sub-key mapping: tiap jenis kendaraan milik objek ini diarahkan
            // ke parent-key-nya. Field non-kendaraan (`total_survei`,
            // `user_id`) tidak pernah muncul di sini, sehingga otomatis
            // tertolak di bawah pada cek `$subKeyMap`.
            foreach (ObjekKunci::daftarKunci((string)$itemId, $item) as $subKey) {
                $subKeyMap[$subKey] = $parentKey;
            }
        }

        // 2. Ambil Semua Data Survei
        $surveiHarianRaw = $this->database->getReference('survei_harian')->getValue() ?? [];

        // 2b. Deteksi kunci kendaraan yang ADA di data tapi TIDAK dimiliki
        // objek tarif mana pun.
        //
        // Tanpa langkah ini, field seperti `motor` yang tidak punya master
        // akan tersaring diam-diam oleh cek `$subKeyMap` di bawah, sehingga
        // angkanya hilang dari kartu, grafik, dan tabel pendapatan tanpa
        // jejak. Data turunannya sudah ada di memori, jadi pemindaian ini
        // tidak menambah pembacaan Firebase.
        //
        // Kuncinya sengaja diambil dari `ObjekKunci::kunciSurvei` supaya
        // field non-kendaraan (`total_survei`, `user_id`, `updated_at`)
        // tidak ikut terhitung sebagai kendaraan.
        $volumePerKunci = [];
        foreach ($surveiHarianRaw as $dataTanggal) {
            if (!is_array($dataTanggal)) continue;

            foreach ($dataTanggal as $dataJam) {
                if (!is_array($dataJam)) continue;

                foreach ($dataJam as $item) {
                    if (!is_array($item)) continue;

                    foreach (ObjekKunci::kunciSurvei($item) as $fk => $ignored) {
                        $vol = (int) ($item[$fk] ?? 0);
                        if ($vol <= 0) continue;
                        $volumePerKunci[$fk] = ($volumePerKunci[$fk] ?? 0) + $vol;
                    }
                }
            }
        }

        // Kunci tak-terpetakan tetap ikut dihitung dan ikut ditampilkan,
        // hanya diberi label "belum terpetakan" supaya jelas itu masalah
        // master, bukan kendaraan tambahan yang nyata.
        //
        // Harganya 0 karena tarifnya memang tidak diketahui, sehingga tidak
        // ada pendapatan yang dikarang. Yang hilang cuma angka kendaraan,
        // bukan kesesuan keuangan. Peta `subKeyMap` diarahkan ke dirinya
        // sendiri supaya baris loop di bawah tetap bisa mengaccumulasinya
        // tanpa perlu cabang khusus.
        $takTerpetakan = ObjekKunci::takTerpetakan($volumePerKunci, $tarifRaw);
        foreach ($takTerpetakan as $uKey => $uVol) {
            $uKey = (string) $uKey;
            $tarifMap[$uKey]   = 0;
            $objekNames[$uKey] = ObjekKunci::labelBelumTerpetakan($uKey);
            $subKeyMap[$uKey]  = $uKey;
            $validKeys[]       = $uKey;
        }
        
        $totalPendapatan = 0;
        $detailPendapatan = [];
        $groupedSurvei = []; // Untuk chart & stats [tanggal][kunci]

        // 3. Proses Data — Struktur Firebase: survei_harian/{id_lokasi}/{tanggal}/{jam}/{id_penugasan}/{data}
        foreach ($surveiHarianRaw as $idLokasi => $dataTanggal) {
            if (!is_array($dataTanggal)) continue;

            foreach ($dataTanggal as $tgl => $dataJam) {
                if (!is_array($dataJam)) continue;

                // Inisialisasi tanggal ini jika belum ada
                if (!isset($groupedSurvei[$tgl])) {
                    $groupedSurvei[$tgl] = [];
                    foreach ($validKeys as $vk) $groupedSurvei[$tgl][$vk] = 0;
                }

                // Loop JAM (mis: "08", "09", ...)
                foreach ($dataJam as $jam => $dataPerJam) {
                    if (!is_array($dataPerJam)) continue;

                    // Loop ID_PENUGASAN dalam jam ini
                    foreach ($dataPerJam as $idPenugasan => $item) {
                        if (!is_array($item)) continue;

                        // Iterasi semua field di item, petakan ke parent-key via subKeyMap
                        // Ini menggabungkan "minibus" + "pick-up" ke satu bucket parent-nya
                        foreach ($item as $fieldKey => $vol) {
                            $vol = (int)$vol;
                            if ($vol <= 0) continue;
                            if (!isset($subKeyMap[$fieldKey])) continue; // bukan field kendaraan

                            $parentKey = $subKeyMap[$fieldKey];

                            // Inisialisasi aman untuk multi-lokasi
                            if (!isset($groupedSurvei[$tgl][$parentKey])) {
                                $groupedSurvei[$tgl][$parentKey] = 0;
                            }

                            // Akumulasi statistik per tanggal (untuk chart)
                            $groupedSurvei[$tgl][$parentKey] += $vol;

                            // Akumulasi untuk hari ini (pendapatan & tabel detail)
                            if ($tgl === $hariIni) {
                                $harga    = $tarifMap[$parentKey] ?? 0;
                                $subTotal = $vol * $harga;
                                $totalPendapatan += $subTotal;

                                $keyDetail = $idLokasi . '_' . $parentKey;
                                if (!isset($detailPendapatan[$keyDetail])) {
                                    $detailPendapatan[$keyDetail] = [
                                        'objek'     => $objekNames[$parentKey],
                                        'id_lokasi' => $idLokasi,
                                        'jumlah'    => 0,
                                        'nominal'   => 0
                                    ];
                                }
                                $detailPendapatan[$keyDetail]['jumlah']  += $vol;
                                $detailPendapatan[$keyDetail]['nominal'] += $subTotal;
                            }
                        }
                    }
                }
            }
        }

        // 4. Siapkan Statistik Card (Hanya Hari Ini)
        $stats = [];
        foreach ($validKeys as $key) {
            $stats[$key] = $groupedSurvei[$hariIni][$key] ?? 0;
        }

        // 5. Siapkan Data Chart (Minggu s/d Sabtu pekan ini)
        $labelsMingguan = [];
        $chartData = [];
        foreach ($validKeys as $key) $chartData[$key] = [];

        // Ambil awal minggu ini (dimulai dari hari Minggu)
        $startOfWeek = Carbon::now('Asia/Makassar')->startOfWeek(Carbon::SUNDAY)->setTimezone('Asia/Makassar');

        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startOfWeek)->addDays($i);
            $dateStr = $date->toDateString();
            $labelsMingguan[] = $date->translatedFormat('D'); 

            foreach ($validKeys as $key) {
                $chartData[$key][] = $groupedSurvei[$dateStr][$key] ?? 0;
            }
        }

        // 6. Lengkapi Nama Lokasi untuk Tabel Detail
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        foreach ($detailPendapatan as &$detail) {
            $idL = $detail['id_lokasi'];
            $detail['nama_lokasi'] = $lokasiMaster[$idL]['nama_lokasi'] ?? ($lokasiMaster[$idL]['alamat'] ?? 'Lokasi Tidak Dikenal');
        }

        // 7. Hitung Notifikasi Unread
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        $unreadCount = 0;
        if (is_array($notifRaw)) {
            foreach ($notifRaw as $notif) {
                if (($notif['status'] ?? '') === 'unread') $unreadCount++;
            }
        }

        return view('admin.dashboardadmin', [
            'totalPendapatan' => $totalPendapatan,
            'stats' => $stats,
            'objekNames' => $objekNames,
            'tarifMap' => $tarifMap,
            'subKeyMap' => $subKeyMap,
            'validKeys' => $validKeys,
            'detailPendapatan' => array_values($detailPendapatan),
            'labelsMingguan' => $labelsMingguan,
            'chartData' => $chartData,
            'unreadCount' => $unreadCount,
            'takTerpetakan' => $takTerpetakan
        ]);
    }

    /**
     * Daftar notifikasi untuk lonceng di header.
     *
     * Dipanggil lewat AJAX, jadi mengembalikan JSON.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotifications()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        krsort($notifRaw);
        return response()->json($notifRaw);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     *
     * Menulis field `status` per notifikasi dengan operasi `set`, jadi
     * field lain pada notifikasi tersebut tidak ikut berubah.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationsRead()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        foreach ($notifRaw as $key => $notif) {
            $this->database->getReference('notifikasi/' . $key . '/status')->set('read');
        }
        return response()->json(['success' => true]);
    }
}
