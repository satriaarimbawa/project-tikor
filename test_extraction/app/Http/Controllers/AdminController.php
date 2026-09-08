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
        Carbon::setLocale('id');
        $waktuSekarang = Carbon::now('Asia/Makassar');
        $hariIni = $waktuSekarang->toDateString();

        // 1. Ambil Master Tarif & Inisialisasi Kunci Valid
        $tarifRaw = $this->database->getReference('objek_tarif')->getValue() ?? [];
        $tarifMap = [];
        $objekNames = [];
        $validKeys = [];

        foreach ($tarifRaw as $item) {
            $namaOriginal = $item['nama'] ?? 'Lainnya';
            // Normalisasi kunci: lowercase dan hapus spasi
            $key = strtolower(str_replace(' ', '', $namaOriginal));
            $tarifMap[$key] = (int)($item['harga'] ?? 0);
            $objekNames[$key] = $namaOriginal;
            $validKeys[] = $key;
        }

        // 2. Ambil Semua Data Survei
        $surveiHarianRaw = $this->database->getReference('survei_harian')->getValue() ?? [];
        
        $totalPendapatan = 0;
        $detailPendapatan = [];
        $groupedSurvei = []; // Untuk chart & stats [tanggal][kunci]

        // 3. Proses Data dengan Struktur: survei_harian -> id_lokasi -> tanggal -> jam -> id_penugasan
        foreach ($surveiHarianRaw as $idLokasi => $dataTanggal) {
            if (!is_array($dataTanggal)) continue;

            foreach ($dataTanggal as $tgl => $dataJam) {
                if (!is_array($dataJam)) continue;
                
                if (!isset($groupedSurvei[$tgl])) {
                    foreach ($validKeys as $vk) $groupedSurvei[$tgl][$vk] = 0;
                }

                foreach ($dataJam as $jam => $dataPenugasan) {
                    if (!is_array($dataPenugasan)) continue;

                    // Support baik struktur langsung (ada user_id) maupun berjenjang (id_penugasan)
                    $itemsToProcess = [];
                    if (isset($dataPenugasan['user_id'])) {
                        $itemsToProcess[] = $dataPenugasan;
                    } else {
                        foreach ($dataPenugasan as $item) {
                            if (is_array($item)) $itemsToProcess[] = $item;
                        }
                    }

                    foreach ($itemsToProcess as $item) {
                        foreach ($validKeys as $key) {
                            $vol = (int)($item[$key] ?? 0);
                            if ($vol > 0) {
                                // Akumulasi untuk statistik (berdasarkan tanggal)
                                $groupedSurvei[$tgl][$key] += $vol;

                                // Akumulasi untuk hari ini (pendapatan & detail)
                                if ($tgl === $hariIni) {
                                    $harga = $tarifMap[$key] ?? 0;
                                    $subTotal = $vol * $harga;
                                    $totalPendapatan += $subTotal;

                                    $keyDetail = $idLokasi . '_' . $key;
                                    if (!isset($detailPendapatan[$keyDetail])) {
                                        $detailPendapatan[$keyDetail] = [
                                            'objek' => $objekNames[$key],
                                            'id_lokasi' => $idLokasi,
                                            'jumlah' => 0,
                                            'nominal' => 0
                                        ];
                                    }
                                    $detailPendapatan[$keyDetail]['jumlah'] += $vol;
                                    $detailPendapatan[$keyDetail]['nominal'] += $subTotal;
                                }
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

        // 5. Siapkan Data Chart (6 Hari Terakhir)
        $labelsMingguan = [];
        $chartData = [];
        foreach ($validKeys as $key) $chartData[$key] = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Makassar')->subDays($i);
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
            'detailPendapatan' => array_values($detailPendapatan),
            'labelsMingguan' => $labelsMingguan,
            'chartData' => $chartData,
            'unreadCount' => $unreadCount
        ]);
    }

    public function getNotifications()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        krsort($notifRaw);
        return response()->json($notifRaw);
    }

    public function markNotificationsRead()
    {
        $notifRaw = $this->database->getReference('notifikasi')->getValue() ?? [];
        foreach ($notifRaw as $key => $notif) {
            $this->database->getReference('notifikasi/' . $key . '/status')->set('read');
        }
        return response()->json(['success' => true]);
    }
}
