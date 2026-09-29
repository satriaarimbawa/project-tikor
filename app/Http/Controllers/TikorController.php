<?php

namespace App\Http\Controllers;

use App\Support\PenugasanWaktu;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class TikorController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        // 1. Ambil semua data lokasi dan penugasan
        $daftarLokasiRaw = $this->database->getReference('lokasi')->getValue() ?? [];
        
        // Urutkan dari yang terbaru
        $daftarLokasiRaw = array_reverse($daftarLokasiRaw, true);
        
        $daftarPenugasan = $this->database->getReference('penugasan')->getValue() ?? [];

        // 2. Identifikasi ID Lokasi mana saja yang sedang aktif
        $lokasiAktifIds = PenugasanWaktu::idLokasiBerjalan($daftarPenugasan);

        // 3. Tambahkan atribut status 'Aktif'/'Inaktif' secara dinamis
        $dataFinal = [];
        foreach ($daftarLokasiRaw as $key => $lokasi) {
            $lokasi['status_dinamis'] = in_array((string)$key, $lokasiAktifIds) ? 'Aktif' : 'Inaktif';
            $dataFinal[] = array_merge($lokasi, ['id' => $key]);
        }

        // Pagination Manual
        $perPage = (int) $request->input('perPage', 5);
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($dataFinal);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($dataFinal, $offset, $perPage);

        return view('admin.tikor.penetapanlokasi', [
            'daftarLokasi' => $dataPaginated,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string',
            'koordinat'   => ['required', 'string', 'regex:/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/'],
            'target_harian' => 'required',
        ], [
            'nama_lokasi.required' => 'Nama lokasi wajib diisi.',
            'koordinat.required'   => 'Titik koordinat harus ditentukan melalui peta.',
            'koordinat.regex'      => 'Format koordinat tidak valid (harus latitude,longitude).',
            'target_harian.required' => 'Target harian wajib diisi.',
        ]);

        $coords = explode(',', $request->input('koordinat'));
        $latitude = trim($coords[0]);
        $longitude = trim($coords[1]);

        // Bersihkan format uang (titik) menjadi angka murni
        $targetHarianClean = (int) preg_replace('/[^0-9]/', '', $request->input('target_harian'));

        $dataLokasi = [
            'nama_lokasi'   => $request->input('nama_lokasi'),
            'koordinat'     => $request->input('koordinat'),
            'latitude'      => (float) $latitude,
            'longitude'     => (float) $longitude,
            'radius'        => 100, 
            'target_harian' => $targetHarianClean,
            'created_at'    => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s')
        ];

        try {
            $this->database->getReference('lokasi')->push($dataLokasi);
            return redirect()->to('/dashboard-tikor')->with('success', 'Lokasi berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->database->getReference('lokasi/' . $id)->remove();
            return redirect()->back()->with('success', 'Lokasi berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
