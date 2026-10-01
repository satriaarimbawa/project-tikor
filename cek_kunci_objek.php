<?php
/**
 * CEK KUNCI OBJEK TARIFF - SCRIPT READ-ONLY / DIAGNOSTIK
 *
 * TUJUAN
 *   Menemukan kenapa data kendaraan (terutama "Sepeda Motor") yang SUDAH
 *   ADA di `survei_harian` tidak terbaca di halaman mana pun.
 *
 * KEAMANAN
 *   - HANYA MEMBACA. Tidak ada set(), update(), push(), remove().
 *   - Tidak menyentuh Laravel DB / MySQL sama sekali.
 *   - Tidak mengubah data Firebase satu byte pun.
 *
 * CARA PAKAI
 *   php cek_kunci_objek.php
 *
 * KONSEP KUNCI
 *   Ada DUA sumber kunci yang harus selalu sama:
 *
 *   A. Kunci MASTER  = ObjekKunci::untuk($nodeId, $row)
 *      dari field `kunci`, atau PETA_LEGACY, atau `nama`.
 *      Dipakai semua halaman laporan.
 *
 *   B. Kunci TULIS   = ObjekKunci::dariNama(penugasan.objek_survei)
 *      dari NAMA yang dibekukan saat penugasan dibuat.
 *      Dipakai OperatorController::simpanHitung().
 *
 *   Tidak ada kode yang menjamin A dan B sama. Begitu admin me-rename
 *   objek, keduanya bisa berbeda dan data jadi yatim.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Kreait\Firebase\Contract\Database;
use App\Support\ObjekKunci;
use Carbon\Carbon;

$db = app(Database::class);
$now = Carbon::now('Asia/Makassar');
$today = $now->toDateString();

function garis(string $t = ''): void
{
    echo $t . str_repeat('=', max(0, 68 - strlen($t))) . PHP_EOL;
}

garis('CEK KUNCI OBJEK TARIFF');
echo "Waktu server  : " . $now->toDateTimeString() . " (WITA)" . PHP_EOL;
echo "Tanggal data  : " . $today . PHP_EOL;
echo "MODE          : READ-ONLY (tidak ada operasi tulis)" . PHP_EOL;

// ---------------------------------------------------------------
// 1. Master objek_tarif
// ---------------------------------------------------------------
$tarifRaw = $db->getReference('objek_tarif')->getValue() ?? [];

garis('1. MASTER objek_tarif');

if (!$tarifRaw) {
    echo "[PENTING] objek_tarif KOSONG. Semua data pasti tidak tampil." . PHP_EOL;
}

$keyPemilik = [];   // kunci data => node id yang "menang"
$daftarNode = [];   // node id => info

foreach ($tarifRaw as $id => $row) {
    if (!is_array($row)) {
        continue;
    }
    $nama = (string)($row['nama'] ?? '(tanpa nama)');
    $kunciRaw = trim((string)($row['kunci'] ?? ''));
    $kunciEfektif = ObjekKunci::untuk((string)$id, $row);
    $bentrok = isset($keyPemilik[$kunciEfektif]);

    printf(
        "  %-14s nama=%-18s kunci_field=%-14s => kunci_efektif=%-16s %s%s",
        $id,
        '"' . $nama . '"',
        $kunciRaw === '' ? '(kosong)' : '"' . $kunciRaw . '"',
        '"' . $kunciEfektif . '"',
        $kunciRaw !== '' ? '[pakai field kunci]' : '[pakai fallback nama/legacy]',
        $bentrok ? '  <-- BENTROK, node "' . $keyPemilik[$kunciEfektif] . '" juga punya kunci ini' : ''
    );
    PHP_EOL;

    $daftarNode[(string)$id] = [
        'nama'   => $nama,
        'kunci'  => $kunciEfektif,
        'harga'  => (int)($row['harga'] ?? 0),
        'status' => (string)($row['status'] ?? ''),
    ];

    if (!$bentrok && $kunciEfektif !== '') {
        $keyPemilik[$kunciEfektif] = (string)$id;
    }
}

echo PHP_EOL . "  Kunci yang dimiliki master : " . (implode(', ', array_keys($keyPemilik)) ?: '(tidak ada)') . PHP_EOL;

// ---------------------------------------------------------------
// 2. Kunci yang benar-benar ada di data survei (7 hari + hari ini)
// ---------------------------------------------------------------
$survei = $db->getReference('survei_harian')->getValue() ?? [];

$tanggalDipakai = [];
for ($mundur = 0; $mundur < 7; $mundur++) {
    $tanggalDipakai[] = Carbon::now('Asia/Makassar')->subDays($mundur)->toDateString();
}

$jumlahRecord = 0;
$jumlahRecordHariIni = 0;
$volPerKunci = [];
$volHariIni = [];
$totalSurveiField = 0;

foreach ($tanggalDipakai as $tgl) {
    $hariIni = ($tgl === $today);

    foreach ($survei as $perTanggal) {
        if (!is_array($perTanggal) || !isset($perTanggal[$tgl])) {
            continue;
        }
        foreach ((array)$perTanggal[$tgl] as $perPenugasan) {
            if (!is_array($perPenugasan)) {
                continue;
            }
            foreach ($perPenugasan as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $jumlahRecord++;
                if ($hariIni) {
                    $jumlahRecordHariIni++;
                }
                $totalSurveiField += (int)($item['total_survei'] ?? 0);

                foreach (ObjekKunci::kunciSurvei((array)$item) as $k => $abaikan) {
                    $v = (int)$item[$k];
                    $volPerKunci[$k] = ($volPerKunci[$k] ?? 0) + $v;
                    if ($hariIni) {
                        $volHariIni[$k] = ($volHariIni[$k] ?? 0) + $v;
                    }
                }
            }
        }
    }
}

garis('2. DATA SURVEI');
echo "  Rentang diperiksa  : " . end($tanggalDipakai) . " s/d " . $tanggalDipakai[0] . PHP_EOL;
echo "  Record ditemukan   : " . $jumlahRecord . " total, " . $jumlahRecordHariIni . " di hari ini" . PHP_EOL;
echo "  Field total_survei : " . $totalSurveiField . " (penjumlahan dari field yang tersimpan)" . PHP_EOL;
echo PHP_EOL;

if (!$volPerKunci) {
    echo "  [Tidak ada data survei sama sekali dalam 7 hari terakhir.]" . PHP_EOL;
} else {
    arsort($volPerKunci);
    printf("  %-20s %-10s %-12s %s" . PHP_EOL, 'KUNCI DI DATA', '7 HARI', 'HARI INI', 'STATUS');
    foreach ($volPerKunci as $k => $v) {
        $punyaMaster = isset($keyPemilik[$k]);
        printf(
            "  %-20s %-10d %-12d %s",
            $k,
            $v,
            $volHariIni[$k] ?? 0,
            $punyaMaster
                ? 'ok, dimiliki node "' . $keyPemilik[$k] . '"'
                : '*** TIDAK ADA DI MASTER -> dibuang diam-diam ***'
        );
        PHP_EOL;
    }
}

// ---------------------------------------------------------------
// 3. KUNCI TULIS: apa yang sebenarnya ditulis operator
//
// Ini bagian paling penting. Kunci yang ditulis ke survei_harian
// berasal dari `penugasan.objek_survei`, yaitu NAMA yang dibekukan
// saat penugasan dibuat - BUKAN dari field `kunci` di master.
// ---------------------------------------------------------------
$penugasanRaw = $db->getReference('penugasan')->getValue() ?? [];

$kunciTulis = [];   // kunci tulis => ['nama' => nama beku, 'jumlah' => n penugasan]

foreach ($penugasanRaw as $p) {
    if (!is_array($p)) {
        continue;
    }
    $raw = (string)($p['objek_survei'] ?? '');
    foreach (array_filter(array_map('trim', explode(',', $raw))) as $obj) {
        $k = ObjekKunci::dariNama($obj);
        if ($k === '') {
            continue;
        }
        if (!isset($kunciTulis[$k])) {
            $kunciTulis[$k] = ['nama' => $obj, 'jumlah' => 0];
        }
        $kunciTulis[$k]['jumlah']++;
    }
}

garis('3. KUNCI YANG AKAN DITULIS DARI PENUGASAN');
echo "  Ini kunci yang dipakai tombol hitung di layar operator." . PHP_EOL;
echo "  Bandingkan dengan kunci master di bagian 1. Kalau beda," . PHP_EOL;
echo "  data yang baru masuk akan ikut hilang." . PHP_EOL;
echo PHP_EOL;

if (!$kunciTulis) {
    echo "  [Tidak ada penugasan dengan objek_survei terisi.]" . PHP_EOL;
} else {
    foreach ($kunciTulis as $k => $info) {
        $cocok = isset($keyPemilik[$k]);
        printf(
            "  nama_aku=%-18s => kunci_tulis=%-16s dipakai %-4d penugasan  %s",
            '"' . $info['nama'] . '"',
            '"' . $k . '"',
            $info['jumlah'],
            $cocok ? 'COCOK dengan master' : '*** TIDAK COCOK dengan master ***'
        );
        PHP_EOL;
    }
}

// ---------------------------------------------------------------
// 4. PETA KECURIGAOAN
//
// Untuk setiap kunci, dua pertanyaan:
//   (1) Data punya, master punya?  -> normal, tampil
//   (2) Master punya, data punya?  -> normal, tampil
//   (3) Kunci tulis == kunci master? ->normal, data baru aman
// ---------------------------------------------------------------
garis('4. PETA KECURIGAOAN');

$semuaKunci = array_values(array_unique(array_merge(
    array_keys($volPerKunci),
    array_keys($keyPemilik),
    array_keys($kunciTulis)
)));
sort($semuaKunci);

printf("  %-18s %-10s %-10s %-12s %s" . PHP_EOL, 'KUNCI', 'DI DATA', 'DI MASTER', 'DITULIS', 'BAWAH INI');
foreach ($semuaKunci as $k) {
    $diData = isset($volPerKunci[$k]);
    $diMaster = isset($keyPemilik[$k]);
    $ditulis = isset($kunciTulis[$k]);

    if ($diData && $diMaster && $ditulis) {
        $ket = 'SEHAT - muncul di semua halaman';
    } elseif ($diData && !$diMaster) {
        $ket = 'YATIM - data ada, tidak punya master';
    } elseif (!$diData && $diMaster && $ditulis) {
        $ket = 'AMAN - master cocok, data belum ada';
    } elseif (!$diData && $diMaster && !$ditulis) {
        $ket = 'KOSONG - master ada tapi tidak pernah ditulis';
    } elseif ($ditulis && !$diMaster) {
        $ket = 'BAHAYA - ditulis tapi tidak punya master';
    } else {
        $ket = 'hanya di data (kemungkinan data lama)';
    }

    printf(
        "  %-18s %-10s %-10s %-12s %s",
        $k,
        $diData ? (string)$volPerKunci[$k] : '-',
        $diMaster ? $keyPemilik[$k] : '-',
        $ditulis ? 'ya' : 'tidak',
        $ket
    );
    PHP_EOL;
}

// ---------------------------------------------------------------
// 5. VERDICT
// ---------------------------------------------------------------
garis('5. VERDICT');

$yatim = ObjekKunci::takTerpetakan($volPerKunci, $tarifRaw);
$yatimHariIni = ObjekKunci::takTerpetakan($volHariIni, $tarifRaw);
$ditulisYatim = array_diff(array_keys($kunciTulis), array_keys($keyPemilik));

if (!$yatim && !$ditulisYatim) {
    echo "  Semua kunci punya master dan semua kunci tulis cocok." . PHP_EOL;
    echo "  Kalau angka tetap salah di layar, penyebabnya bukan master tarif." . PHP_EOL;
    echo "  Periksa dua hal lain:" . PHP_EOL;
    echo "   - bentrok kunci antar node (tanda BENTROK di bagian 1)" . PHP_EOL;
    echo "   - jam 00-09 tidak muncul di PDF (bug warisan str_pad)" . PHP_EOL;
} else {
    if ($yatim) {
        echo "  A. DATA YATIM (sudah ada, tidak tampil di halaman mana pun):" . PHP_EOL;
        foreach ($yatim as $k => $v) {
            echo "     kunci " . $k . " = " . $v . " unit"
                . (isset($volHariIni[$k]) ? " (hari ini " . $volHariIni[$k] . ")" : " (tidak ada hari ini)")
                . PHP_EOL;
        }
        echo PHP_EOL;
    }

    if ($ditulisYatim) {
        echo "  B. KUNCI TULIS TANPA MASTER (data baru akan langsung hilang):" . PHP_EOL;
        foreach ($ditulisYatim as $k) {
            $nama = $kunciTulis[$k]['nama'] ?? '?';
            echo "     nama \"" . $nama . "\" => kunci \"" . $k . "\", tidak dimiliki master" . PHP_EOL;
        }
        echo PHP_EOL;
    }

    echo "  CARA MEMPERBAIKI (pilih salah satu, JANGAN keduanya):" . PHP_EOL;
    echo PHP_EOL;
    echo "  OPSI 1 - samakan kunci MASTER dengan kunci yang sudah jadi di data." . PHP_EOL;
    echo "    Buka Firebase Console > objek_tarif." . PHP_EOL;
    echo "    Untuk node yang bermasalah, ubah field \"kunci\" jadi:" . PHP_EOL;
    foreach (array_unique(array_merge(array_keys($yatim), array_keys($ditulisYatim))) as $k) {
        echo "        kunci = \"" . $k . "\"" . PHP_EOL;
    }
    echo "    Ini MEMPERTAHANKAN semua data lama dan data baru jadi konsisten." . PHP_EOL;
    echo "    Inilah pilihan yang lebih aman." . PHP_EOL;
    echo PHP_EOL;
    echo "  OPSI 2 - pindahkan semua data lama ke kunci baru." . PHP_EOL;
    echo "    Risiko tinggi. Data lama ditulis ulang, bisa salah atau dobel." . PHP_EOL;
    echo "    Tidak disarankan." . PHP_EOL;

    if ($yatimHariIni) {
        echo PHP_EOL;
        echo "  HARI INI juga terdampak (" . array_sum($yatimHariIni) . " unit), jadi perbaikan" . PHP_EOL;
        echo "  Live Dashboard akan langsung menampilkannya lewat kartu kuning." . PHP_EOL;
    }
}

garis('6. HAL YANG MASIH MEMBUANG DATA TANPA PERINGATAN');
echo "  Perbaikan Live Dashboard sudah ada, tapi halaman lain belum:" . PHP_EOL;
echo "   - AdminController          (dashboard admin)      SUDAH PERLU DIPERBAIKI" . PHP_EOL;
echo "   - LaporanOperatorController (laporan operator)   SUDAH PERLU DIPERBAIKI" . PHP_EOL;
echo "   - LaporanLokasiController   (laporan lokasi)      SUDAH ADA peringatan" . PHP_EOL;
echo "   - LiveDashboardController   (live dashboard)      SUDAH ADA peringatan" . PHP_EOL;
echo "   - OperatorController       (layar survei)        SUDAH PERLU DIPERBAIKI" . PHP_EOL;
echo PHP_EOL;
echo "  Makanya data bisa terlihat benar di satu halaman tapi hilang di lain." . PHP_EOL;

garis();
echo "Selesai. Tidak ada data yang diubah." . PHP_EOL;
