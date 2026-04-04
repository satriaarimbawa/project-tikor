<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;


class PenugasanController extends Controller
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
        retry(100, function() {
            return view('admin.penugasan.index');
        }, 100);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = $this->database->getReference('users')->getValue() ?? [];
        $lokasitikor = $this->database->getReference('pengaturan_lokasi')->getValue() ?? [];
        $users = $this->database->getReference('users')->getValue() ?? [];

        // @dd($listOperatorUid);
        return view('admin.penugasan.form_penugasan', [
            'lokasitikor' => $lokasitikor, 
            'users' => $users,              
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (Pastikan admin tidak mengirim form kosong)
        $request->validate([
            'id_lokasi'     => 'required|string',
            'id_user'       => 'required|string',
            'waktu_mulai'   => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai', // Selesai harus setelah mulai
            'surat_spt'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' // Maksimal 2MB
        ]);

        try {
            // 2. Proses Upload File Surat Tugas (SPT)
            $pathSpt = '';
            // Mengecek apakah ada file yang diunggah
            if ($request->hasFile('surat_spt')) {
                $file = $request->file('surat_spt');
                
                // Membuat nama file unik (gabungan waktu saat ini + nama asli file)
                // Contoh: 1710582000_surattugas_budi.pdf
                $namaFile = time() . '_' . $file->getClientOriginalName(); 
                
                // Menyimpan file secara fisik ke dalam folder public/uploads/spt di project Anda
                $file->move(public_path('uploads/spt'), $namaFile); 
                
                // Menyimpan rute lokasi file untuk ditaruh di Firebase
                $pathSpt = 'uploads/spt/' . $namaFile; 
            }

            // 3. Rapikan Format Waktu (Menggunakan Carbon)
            // Mengubah format bawaan HTML menjadi format baku YYYY-MM-DD HH:MM:SS
            $waktuMulai = \Carbon\Carbon::parse($request->input('waktu_mulai'))->format('Y-m-d H:i:s');
            $waktuSelesai = \Carbon\Carbon::parse($request->input('waktu_selesai'))->format('Y-m-d H:i:s');

            // 4. Bungkus Data ke dalam Array
            $dataPenugasan = [
                'id_lokasi'     => $request->input('id_lokasi'),
                'id_user'       => $request->input('id_user'),
                'waktu_mulai'   => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'surat_spt'     => $pathSpt,
                'dibuat_pada'   => \Carbon\Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s')
            ];

            // 5. Simpan ke Firebase!
            // Menggunakan push() agar data baru ditambahkan ke bawah daftar, bukan menimpa yang lama
            $this->database->getReference('penugasan')->push($dataPenugasan);

            // 6. Arahkan kembali ke Dashboard Admin dengan pesan sukses
            return redirect('/dashboard-admin')->with('success', 'Penugasan operator berhasil disimpan!');

        } catch (\Exception $e) {
            // Jika ada yang error (misal folder upload belum ada atau Firebase down)
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
