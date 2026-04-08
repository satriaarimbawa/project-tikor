<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */

        protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {     
        $userId = session('user_id');
        $riwayatRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];


        $semuaLokasi = $this->database->getReference('pengaturan_lokasi')->getValue() ?? [];
        $idLokasiAktif = session('id_lokasi_aktif');
        $dataLokasi = $this->database->getReference("pengaturan_lokasi/{$idLokasiAktif}")->getValue();
        $namaLokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');
        $riwayatSelesai = [];
        foreach ($riwayatRaw as $key => $item) {
                $idLokasi = $item['id_lokasi'] ?? null;
                
                $namaLokasi = isset($semuaLokasi[$idLokasi]) 
                            ? ($semuaLokasi[$idLokasi]['alamat'] ?? 'Lokasi Tidak Dikenal') 
                            : 'ID Lokasi Tidak Ditemukan';

                $item['nama_lokasi_display'] = $namaLokasi;
                $riwayatSelesai[$key] = $item;
            }
        $namaLokasi = isset($semuaLokasi[$idLokasi]) 
            ? ($semuaLokasi[$idLokasi]['alamat'] ?? 'Lokasi Tidak Dikenal') 
            : 'ID Lokasi Tidak Ditemukan';

        $item['nama_lokasi_display'] = $namaLokasi;

        $uid = session('user_id'); // UID user (misal: -Oo7v...)
        $idLokasi = session('id_lokasi_aktif'); // ID Lokasi aktif (misal: -OpRHw...)

        // 1. Ambil semua data di bawah lokasi tersebut
        $dataLokasi = $this->database->getReference('survei_harian/' . $idLokasi)->getValue() ?? [];

        $riwayatKendaraan = [];

        // 2. Lakukan perulangan untuk setiap tanggal
        foreach ($dataLokasi as $tanggal => $dataPerTanggal) {
            // 3. Cek apakah di tanggal tersebut ada data milik UID user ini
            if (isset($dataPerTanggal[$uid])) {
                $record = $dataPerTanggal[$uid];
                
                // Tambahkan info tanggal ke dalam record agar bisa ditampilkan di tabel
                $record['tanggal_survei'] = $tanggal;
                $riwayatKendaraan[] = $record;
            }
        }

        // Urutkan berdasarkan tanggal terbaru (opsional)
        usort($riwayatKendaraan, function($a, $b) {
            return strcmp($b['tanggal_survei'], $a['tanggal_survei']);
        });

        return view('operator.index',[
            'riwayat' => $riwayatSelesai,
            'nama_lokasi' => $namaLokasi,
            'riwayat_kendaraan' => $riwayatKendaraan
        ]);
    }
    public function profile()
    {     
        return view('operator.profile',[
            'user_id' => session('user_id'),
            'nama_lokasi' => session('nama_lokasi_aktif')
        ]);
    }

    public function penugasan()
    {     
        $userId = session('user_id');
        $riwayatRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];
        $semuaLokasi = $this->database->getReference('pengaturan_lokasi')->getValue() ?? [];
        $idLokasiAktif = session('id_lokasi_aktif');
        $dataLokasi = $this->database->getReference("pengaturan_lokasi/{$idLokasiAktif}")->getValue();
        $namaLokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');
        $riwayatSelesai = [];
            foreach ($riwayatRaw as $key => $item) {
                $idLokasi = $item['id_lokasi'] ?? null;
                
                $namaLokasi = isset($semuaLokasi[$idLokasi]) 
                            ? ($semuaLokasi[$idLokasi]['alamat'] ?? 'Lokasi Tidak Dikenal') 
                            : 'ID Lokasi Tidak Ditemukan';

                $item['nama_lokasi_display'] = $namaLokasi;
                $riwayatSelesai[$key] = $item;
            }
                return view('operator.penugasan', [
                    'riwayat' => $riwayatSelesai,
                    'namaLokasi' => $namaLokasi
                ]);
        return view('operator.penugasan');
    }

    public function survei()
    {  
  $userId = session('user_id');
    $idLokasiAktif = session('id_lokasi_aktif'); 
    $tanggal = date('Y-m-d');

    // Ambil data survei yang sudah tersimpan di Firebase untuk user ini hari ini
    $pathSurvei = "survei_harian/{$idLokasiAktif}/{$tanggal}/{$userId}";
    $dataSurvei = $this->database->getReference($pathSurvei)->getValue() ?? [];
    $dataLokasi = $this->database->getReference("pengaturan_lokasi/{$idLokasiAktif}")->getValue();
    

    $namaLokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');
    // Ambil riwayat penugasan seperti biasa
    $riwayatRaw = $this->database->getReference('penugasan')
                ->orderByChild('id_user')
                ->equalTo($userId)
                ->getValue() ?? [];

    return view('operator.survei', [
        'riwayat' => $riwayatRaw,
        'idLokasi' => $idLokasiAktif,
        'user_id' => $userId,
        'dataSurvei' => $dataSurvei, // Kirim data survei ke Blade
        'namaLokasi' => $namaLokasi
    ]);
    }

    public function simpanHitung(Request $request)
{
   $jenis = $request->input('jenis_kendaraan');
    
    // Ambil ID Lokasi & User dari session
    $idLokasi = session('id_lokasi_aktif'); 
    $userId = session('user_id');
    $tanggal = date('Y-m-d');

    // PROTEKSI: Jika session kosong, data tidak akan masuk
    if (!$idLokasi || !$userId) {
        return response()->json([
            'status' => 'error', 
            'message' => 'Sesi berakhir atau lokasi tidak terdeteksi. Silakan login ulang.',
            'debug' => ['id_lokasi' => $idLokasi, 'user_id' => $userId]
        ], 400);
    }

    $path = "survei_harian/{$idLokasi}/{$tanggal}/{$userId}";
    $reference = $this->database->getReference($path);
    
    // Ambil data lama dulu
    $currentData = $reference->getValue();

    $newCount = ($currentData[$jenis] ?? 0) + 1;
    $newTotal = ($currentData['total_survei'] ?? 0) + 1;

    // Simpan/Update
    $reference->update([
        $jenis => $newCount,
        'total_survei' => $newTotal,
        'user_id' => $userId,
        'id_lokasi' => $idLokasi,
        'updated_at' => date('H:i:s')
    ]);

    return response()->json([
        'status' => 'success', 
        'new_count' => $newCount,
        'lokasi' => $idLokasi
    ]);

    return response()->json(['status' => 'success', 'debug_id_lokasi' => $idLokasi]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}