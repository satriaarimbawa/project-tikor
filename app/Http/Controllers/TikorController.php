<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class TikorController extends Controller
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
        $lokasiKantor = $this->database->getReference('pengaturan_lokasi')->getValue();

        return view('admin.tikor.dashboard', [
            'lokasiKantor' => $lokasiKantor
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tikor.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:10',
            'alamat' => 'required|string|nullable'
        ]);

        // 2. Siapkan data yang akan dikirim ke Firebase
        $dataLokasi = [
            'latitude' => (float) $request->input('latitude'),
            'longitude' => (float) $request->input('longitude'),
            'radius' => (int) $request->input('radius'),
            'alamat' => $request->input('alamat')
        ];

        try {
            // 3. Simpan atau perbarui (Overwrite) node 'pengaturan_lokasi' di Firebase
            // Kita pakai fungsi set() agar datanya langsung tertimpa dengan yang baru
            $this->database->getReference('pengaturan_lokasi')->push($dataLokasi);

            // 4. Kembali ke halaman dengan pesan sukses
            return redirect()->back()->with('success', 'Titik lokasi kantor berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan lokasi: ' . $e->getMessage());
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
