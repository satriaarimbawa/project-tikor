<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class OperatorController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        $userId = session('user_id');
        
        // Ambil riwayat penugasan untuk user ini
        $riwayatRaw = $this->database->getReference('penugasan')
                            ->orderByChild('id_user')
                            ->equalTo($userId)
                            ->getValue() ?? [];

        $semuaLokasi = $this->database->getReference('lokasi')->getValue() ?? [];
        $idLokasiAktif = session('id_lokasi_aktif');
        $dataLokasi = $idLokasiAktif ? $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue() : null;
        
        $nama_lokasi = $dataLokasi ? ($dataLokasi['nama_lokasi'] ?? $dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal') : 'Lokasi Tidak Aktif';
        
        $riwayat_kendaraan = [];
        foreach ($riwayatRaw as $key => $item) {
            $idLokasi = $item['id_lokasi'] ?? null;
            $namaLokasiRiwayat = 'Lokasi Tidak Ditemukan';
            
            if ($idLokasi && isset($semuaLokasi[$idLokasi])) {
                $namaLokasiRiwayat = $semuaLokasi[$idLokasi]['nama_lokasi'] ?? $semuaLokasi[$idLokasi]['alamat'] ?? 'Tanpa Nama';
            }

            $riwayat_kendaraan[] = [
                'jam' => Carbon::parse($item['created_at'] ?? now())->format('H:i'),
                'kendaraan' => $item['objek_survei'] ?? '-',
                'lokasi' => $namaLokasiRiwayat,
            ];
        }

        return view('operator.index', [
            'nama_lokasi' => $nama_lokasi,
            'riwayat_kendaraan' => $riwayat_kendaraan
        ]);
    }

    public function penugasan()
    {
        $userId = session('user_id');
        $tugasRaw = $this->database->getReference('penugasan')
                         ->orderByChild('id_user')
                         ->equalTo($userId)
                         ->getValue() ?? [];

        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];

        $daftarTugas = [];
        foreach ($tugasRaw as $key => $tugas) {
            $idLokasi = $tugas['id_lokasi'] ?? null;
            $daftarTugas[] = [
                'id_lokasi' => $idLokasi,
                'nama_lokasi' => $lokasiMaster[$idLokasi]['nama_lokasi'] ?? ($lokasiMaster[$idLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan'),
                'waktu_mulai' => $tugas['waktu_mulai'],
                'waktu_selesai' => $tugas['waktu_selesai'],
                'keterangan' => $tugas['keterangan'] ?? '-',
                'file_spt' => $tugas['file_spt'] ?? null
            ];
        }

        return view('operator.penugasan', ['daftarTugas' => $daftarTugas]);
    }

    public function survei()
    {
        $idLokasiAktif = session('id_lokasi_aktif');
        if (!$idLokasiAktif) {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Silakan pilih lokasi penugasan terlebih dahulu.');
        }

        $dataLokasi = $this->database->getReference("lokasi/{$idLokasiAktif}")->getValue();
        $nama_lokasi = $dataLokasi['nama_lokasi'] ?? ($dataLokasi['alamat'] ?? 'Lokasi Tidak Dikenal');

        return view('operator.survei', [
            'nama_lokasi' => $nama_lokasi
        ]);
    }

    public function simpanHitung(Request $request)
    {
        $idLokasiAktif = session('id_lokasi_aktif');
        $userId = session('user_id');

        $data = [
            'id_user' => $userId,
            'id_lokasi' => $idLokasiAktif,
            'counts' => $request->counts,
            'created_at' => now()->toDateTimeString()
        ];

        $this->database->getReference('hasil_survei')->push($data);

        return response()->json(['success' => true]);
    }

    public function profile()
    {
        $userId = session('user_id');
        $user = $this->database->getReference("users/{$userId}")->getValue();

        return view('operator.profile', ['user' => $user]);
    }
}
