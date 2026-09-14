<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PenugasanController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index(Request $request)
    {
        $tugas = $this->database->getReference('penugasan')->getValue() ?? [];

        // Urutkan dari yang terbaru
        $tugas = array_reverse($tugas, true);

        $users = $this->database->getReference('users')->getValue() ?? [];
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        
        $searchTerm = strtolower($request->input('search', ''));
 
        $dataFinal = [];

        foreach ($tugas as $idPenugasan => $data) {
            $uidUser = $data['id_user'] ?? null;
            $uidLokasi = $data['id_lokasi'] ?? null;

            $namaOperator = $users[$uidUser]['username'] ?? 'User Tidak Ditemukan';
            $namaLokasi = $lokasiMaster[$uidLokasi]['nama_lokasi'] ?? ($lokasiMaster[$uidLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan');
            $objekSurvei = $data['objek_survei'] ?? '-';
            
            // Ambil status asli dari DB, jika kosong default ke 'aktif'
            $rawStatus = isset($data['status']) ? strtolower($data['status']) : 'aktif';

            // Filter Pencarian (Server-side)
            if ($searchTerm !== '') {
                $match = str_contains(strtolower($namaOperator), $searchTerm) || 
                         str_contains(strtolower($namaLokasi), $searchTerm) || 
                         str_contains(strtolower($objekSurvei), $searchTerm) ||
                         str_contains($rawStatus, $searchTerm);
                
                if (!$match) continue;
            }

            $rawMulai = $data['waktu_mulai'] ?? now()->toDateTimeString();
            $rawSelesai = $data['waktu_selesai'] ?? now()->toDateTimeString();
            
            $tglMulai = Carbon::parse($rawMulai)->locale('id')->translatedFormat('d F Y');
            $tglSelesai = Carbon::parse($rawSelesai)->locale('id')->translatedFormat('d F Y');
            
            $dataFinal[] = [
                'id' => $idPenugasan,
                'nama_operator' => $namaOperator,
                'nama_lokasi'   => $namaLokasi,
                'waktu_mulai'   => $rawMulai,
                'file_spt'      => $data['file_spt'] ?? '-',
                'objek_survei'  => $objekSurvei,
                'tanggal_rentang' => $tglMulai . ' s/d ' . $tglSelesai,
                'jam_rentang' => Carbon::parse($rawMulai)->format('H:i') . ' - ' . Carbon::parse($rawSelesai)->format('H:i') . ' WITA',
                'status'        => $rawStatus,
                'created_at_raw' => $data['created_at'] ?? '2000-01-01 00:00:00'
            ];
        }

        // 1. Sorting Multi-Level: Aktif di atas, lalu Created At Terbaru (DESC)
        usort($dataFinal, function($a, $b) {
            // Prioritas status 'aktif'
            if ($a['status'] === 'aktif' && $b['status'] !== 'aktif') return -1;
            if ($a['status'] !== 'aktif' && $b['status'] === 'aktif') return 1;
            
            // Jika status sama, urutkan berdasarkan created_at terbaru
            return strtotime($b['created_at_raw']) <=> strtotime($a['created_at_raw']);
        });

        // 2. Pagination Manual
        $perPage = (int) $request->input('perPage', 5);
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($dataFinal);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($dataFinal, $offset, $perPage);

        return view('admin.penugasan.index', [
            'dataPenugasan' => $dataPaginated,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'searchTerm' => $searchTerm,
            'perPage' => $perPage
        ]);
    }

    public function create()
    {
        $users = $this->database->getReference('users')->getValue() ?? [];
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $objekTarif = $this->database->getReference('objek_tarif')->getValue() ?? [];

        return view('admin.penugasan.form_penugasan', [
            'lokasitikor' => $lokasiMaster, 
            'users' => $users,
            'objekTarif' => $objekTarif,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'id_lokasi' => 'required',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'objek_terpilih' => 'required',
            'surat_spt' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ], [
            'id_user.required' => 'Silakan pilih operator.',
            'id_lokasi.required' => 'Silakan pilih lokasi.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'objek_terpilih.required' => 'Silakan pilih minimal satu objek survei.',
            'surat_spt.required' => 'File SPT wajib diunggah.',
            'surat_spt.mimes' => 'Format file SPT harus PDF, JPG, atau PNG.',
            'surat_spt.max' => 'Ukuran file SPT maksimal adalah 2MB.',
        ]);

        try {
            $file = $request->file('surat_spt');
            $ext = $file->getClientOriginalExtension();
            $namaFile = time() . '_' . Str::random(16) . '.' . ($ext ?: 'pdf');
            
            // Pastikan folder upload ada, jika tidak ada buat otomatis
            $destinationPath = public_path('uploads/spt');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Simpan ke folder lokal public/uploads/spt
            $file->move($destinationPath, $namaFile);

            $dataPenugasan = [
                'id_user'       => $request->id_user,
                'id_lokasi'     => $request->id_lokasi,
                'waktu_mulai'   => Carbon::parse($request->waktu_mulai)->format('Y-m-d H:i:s'),
                'waktu_selesai' => Carbon::parse($request->waktu_selesai)->format('Y-m-d H:i:s'),
                'objek_survei'  => $request->objek_terpilih,
                'file_spt'      => $namaFile,
                'keterangan'    => $request->keterangan ?? '-',
                'status'        => 'aktif',
                'created_at'    => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s'),
            ];

            $this->database->getReference('penugasan')->push($dataPenugasan);

            return redirect()->to('/dashboard-penugasan')->with('success', 'Penugasan berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $penugasan = $this->database->getReference('penugasan/' . $id)->getValue();
        if (!$penugasan) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $users = $this->database->getReference('users')->getValue() ?? [];
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
        $objekTarif = $this->database->getReference('objek_tarif')->getValue() ?? [];

        return view('admin.penugasan.form_penugasan', [
            'lokasitikor' => $lokasiMaster,
            'users' => $users,
            'penugasan' => $penugasan,
            'objekTarif' => $objekTarif,
            'id' => $id
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'required',
            'id_lokasi' => 'required',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'objek_terpilih' => 'required',
            'surat_spt' => 'nullable|mimes:pdf,jpg,png|max:2048',
        ], [
            'id_user.required' => 'Silakan pilih operator.',
            'id_lokasi.required' => 'Silakan pilih lokasi.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'objek_terpilih.required' => 'Silakan pilih minimal satu objek survei.',
            'surat_spt.mimes' => 'Format file SPT harus PDF, JPG, atau PNG.',
            'surat_spt.max' => 'Ukuran file SPT maksimal adalah 2MB.',
        ]);

        try {
            $dataPenugasan = $this->database->getReference('penugasan/' . $id)->getValue();
            if (!$dataPenugasan) return redirect()->to('/dashboard-penugasan')->with('error', 'Data tidak ditemukan');

            $updateData = [
                'id_user'       => $request->id_user,
                'id_lokasi'     => $request->id_lokasi,
                'waktu_mulai'   => Carbon::parse($request->waktu_mulai)->format('Y-m-d H:i:s'),
                'waktu_selesai' => Carbon::parse($request->waktu_selesai)->format('Y-m-d H:i:s'),
                'objek_survei'  => $request->objek_terpilih,
                'keterangan'    => $request->keterangan ?? '-',
                'updated_at'    => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s'),
            ];

            if ($request->hasFile('surat_spt')) {
                $file = $request->file('surat_spt');
                $ext = $file->getClientOriginalExtension();
                $namaFile = time() . '_' . Str::random(16) . '.' . ($ext ?: 'pdf');
                
                // Simpan file baru secara lokal
                $file->move(public_path('uploads/spt'), $namaFile);

                // Hapus file lama jika ada dengan sanitasi basename
                if (isset($dataPenugasan['file_spt']) && $dataPenugasan['file_spt'] !== '-') {
                    $oldFileName = basename($dataPenugasan['file_spt']);
                    $oldPath = public_path('uploads/spt/' . $oldFileName);
                    if (file_exists($oldPath) && is_file($oldPath)) unlink($oldPath);
                }

                $updateData['file_spt'] = $namaFile;
            }

            $this->database->getReference('penugasan/' . $id)->update($updateData);

            return redirect()->to('/dashboard-penugasan')->with('success', 'Berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->database->getReference('penugasan/' . $id)->getValue();
            
            // Hapus file lokal dengan sanitasi basename
            if (isset($data['file_spt']) && $data['file_spt'] !== '-') {
                $fileName = basename($data['file_spt']);
                $path = public_path('uploads/spt/' . $fileName);
                if (file_exists($path) && is_file($path)) unlink($path);
            }

            $this->database->getReference('penugasan/' . $id)->remove();
            return redirect()->back()->with('success', 'Penugasan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function resetStatus($id)
    {
        try {
            $today = Carbon::now('Asia/Makassar')->toDateString();
            $this->database->getReference('penugasan/' . $id . '/status')->set('aktif');
            $this->database->getReference("penugasan/{$id}/laporan_harian/{$today}")->remove();
            return redirect()->back()->with('success', 'Status penugasan & kunci laporan hari ini berhasil diaktifkan kembali!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal reset status: ' . $e->getMessage());
        }
    }
}
