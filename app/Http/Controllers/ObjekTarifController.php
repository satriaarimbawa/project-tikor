<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ObjekTarif;

class ObjekTarifController extends Controller
{
    public function index()
    {
        $firebaseData = ObjekTarif::all() ?? [];
        
        $data = [];
        foreach ($firebaseData as $id => $item) {
            $data[] = [
                'id' => $id,
                'nama' => $item['nama'] ?? '-',
                'harga' => $item['harga'] ?? 0,
                'status' => $item['status'] ?? 'Inaktif',
                'keterangan' => $item['keterangan'] ?? ''
            ];
        }

        return view('admin.Objek_Tarif', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'status' => 'required',
        ]);

        $data = [
            'nama' => $request->nama,
            'harga' => (int)$request->harga,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'created_at' => now()->toDateTimeString(),
        ];

        ObjekTarif::create($data);

        return redirect()->back()->with('success', 'Data berhasil disimpan ke Firebase!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'status' => 'required',
        ]);

        $data = [
            'nama' => $request->nama,
            'harga' => (int)$request->harga,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'updated_at' => now()->toDateTimeString(),
        ];

        ObjekTarif::update($id, $data);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        ObjekTarif::delete($id);
        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }
}
