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

        // --- AUTOMATIC CLEANUP: Deactivate expired assignments ---
        try {
            $semuaTugas = $this->database->getReference('penugasan')->getValue() ?? [];
            foreach ($semuaTugas as $keyTugas => $t) {
                if (($t['status'] ?? 'inaktif') === 'aktif') {
                    $selesai = Carbon::parse($t['waktu_selesai'], 'Asia/Makassar');
                    if ($waktuSekarang->gt($selesai)) {
                        // Jika waktu sekarang sudah MELEWATI waktu selesai, ubah status ke inaktif
                        $this->database->getReference("penugasan/{$keyTugas}/status")->set('inaktif');
                    }
                }
            }
        } catch (\Exception $e) {
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

        foreach ($tarifRaw as $item) {
            $namaOriginal = $item['nama'] ?? 'Lainnya';
            $harga        = (int)($item['harga'] ?? 0);

            // Parent key dari nama lengkap (mis: "Mini Bus, Pick-up" → "minibus,pick-up")
            $parentKey = strtolower(str_replace(' ', '', $namaOriginal));

            if (!in_array($parentKey, $validKeys)) {
                $tarifMap[$parentKey]   = $harga;
                $objekNames[$parentKey] = $namaOriginal;
                $validKeys[]            = $parentKey;
            }

            // Buat sub-key mapping untuk tiap bagian nama yang dipisah koma
            // Mis: "Mini Bus, Pick-up" → sub-keys: "minibus" & "pick-up", keduanya → "minibus,pick-up"
            $namaParts = array_map('trim', explode(',', $namaOriginal));
            foreach ($namaParts as $namaPart) {
                if ($namaPart === '') continue;
                $subKey = strtolower(str_replace(' ', '', $namaPart));
                $subKeyMap[$subKey] = $parentKey;
            }
        }

        // 2. Ambil Semua Data Survei
        $surveiHarianRaw = $this->database->getReference('survei_harian')->getValue() ?? [];
        
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
