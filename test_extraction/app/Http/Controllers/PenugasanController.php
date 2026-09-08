<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

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
        $users = $this->database->getReference('users')->getValue() ?? [];
        $lokasiMaster = $this->database->getReference('lokasi')->getValue() ?? [];
 
        $dataFinal = [];

        foreach ($tugas as $idPenugasan => $data) {
            $uidUser = $data['id_user'] ?? null;
            $uidLokasi = $data['id_lokasi'] ?? null;

            $rawMulai = $data['waktu_mulai'] ?? now()->toDateTimeString();
            $rawSelesai = $data['waktu_selesai'] ?? now()->toDateTimeString();
            
            $tglMulai = Carbon::parse($rawMulai)->locale('id')->translatedFormat('d F Y');
            $tglSelesai = Carbon::parse($rawSelesai)->locale('id')->translatedFormat('d F Y');
            
            $dataFinal[] = [
                'id' => $idPenugasan,
                'nama_operator' => $users[$uidUser]['username'] ?? 'User Tidak Ditemukan',
                'nama_lokasi'   => $lokasiMaster[$uidLokasi]['nama_lokasi'] ?? ($lokasiMaster[$uidLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan'),
                'waktu_mulai'   => $rawMulai,
                'file_spt'      => $data['file_spt'] ?? '-',
                'objek_survei'  => $data['objek_survei'] ?? '-',
                'tanggal_rentang' => $tglMulai . ' s/d ' . $tglSelesai,
                'jam_rentang' => Carbon::parse($rawMulai)->format('H:i') . ' - ' . Carbon::parse($rawSelesai)->format('H:i') . ' WITA',
                'status'        => $data['status'] ?? 'aktif',
                'created_at_raw' => $data['created_at'] ?? '2000-01-01 00:00:00'
            ];
        }

        // 1. Sorting: Terbaru di atas (DESC)
        usort($dataFinal, function($a, $b) {
            return strtotime($b['created_at_raw']) <=> strtotime($a['created_at_raw']);
        });

        // 2. Pagination Manual (5 data per halaman)
        $perPage = 5;
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($dataFinal);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($dataFinal, $offset, $perPage);

        return view('admin.penugasan.index', [
            'dataPenugasan' => $dataPaginated,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
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
            'surat_spt' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ], [
            'id_user.required' => 'Silakan pilih operator.',
            'id_lokasi.required' => 'Silakan pilih lokasi.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'surat_spt.required' => 'File SPT wajib diunggah.',
            'surat_spt.mimes' => 'Format file SPT harus PDF, JPG, atau PNG.',
            'surat_spt.max' => 'Ukuran file SPT maksimal adalah 2MB.',
        ]);

        try {
            $file = $request->file('surat_spt');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke folder lokal public/uploads/spt
            $file->move(public_path('uploads/spt'), $namaFile);

            $dataPenugasan = [
                'id_user'       => $request->id_user,
                'id_lokasi'     => $request->id_lokasi,
                'waktu_mulai'   => Carbon::parse($request->waktu_mulai)->format('Y-m-d H:i:s'),
                'waktu_selesai' => Carbon::parse($request->waktu_selesai)->format('Y-m-d H:i:s'),
                'file_spt'      => $namaFile,
                'objek_survei'  => $request->objek_terpilih,
                'keterangan'    => $request->keterangan ?? '-',
                'status'        => 'aktif',
                'created_at'    => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s'),
            ];

            $this->database->getReference('penugasan')->push($dataPenugasan);

            return redirect()->to('/dashboard-penugasan')->with('success', 'Penugasan berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
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

    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_user' => 'required',
            'id_lokasi' => 'required',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'surat_spt' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        try {
            $dataPenugasan = $this->database->getReference('penugasan/' . $id)->getValue();
            if (!$dataPenugasan) return redirect()->back()->with('error', 'Data tidak ditemukan.');

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
                $namaFile = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file baru secara lokal
                $file->move(public_path('uploads/spt'), $namaFile);

                // Hapus file lama jika ada
                if (isset($dataPenugasan['file_spt']) && $dataPenugasan['file_spt'] !== '-') {
                    $oldPath = public_path('uploads/spt/' . $dataPenugasan['file_spt']);
                    if (file_exists($oldPath)) unlink($oldPath);
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
            
            // Hapus file lokal
            if (isset($data['file_spt']) && $data['file_spt'] !== '-') {
                $path = public_path('uploads/spt/' . $data['file_spt']);
                if (file_exists($path)) unlink($path);
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
            $this->database->getReference('penugasan/' . $id . '/status')->set('aktif');
            return redirect()->back()->with('success', 'Status penugasan berhasil diaktifkan kembali!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal reset status: ' . $e->getMessage());
        }
    }
}
