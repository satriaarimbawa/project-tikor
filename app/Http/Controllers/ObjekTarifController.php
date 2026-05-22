<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ObjekTarif;
use App\Services\FirebaseStorageService;

class ObjekTarifController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index()
    {
        $firebaseData = ObjekTarif::all() ?? [];
        
        $data = [];
        foreach ($firebaseData as $id => $item) {
            $iconUrl = isset($item['icon_path']) ? $this->storageService->getPublicUrl($item['icon_path']) : null;
            $data[] = [
                'id' => $id,
                'nama' => $item['nama'] ?? '-',
                'harga' => $item['harga'] ?? 0,
                'status' => $item['status'] ?? 'Inaktif',
                'keterangan' => $item['keterangan'] ?? '',
                'icon_url' => $iconUrl
            ];
        }

        return view('admin.Objek_Tarif', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'status' => 'required',
            'icon' => 'nullable|image|max:1024',
        ]);

        // Bersihkan format uang (titik) menjadi angka murni
        $hargaClean = (int) preg_replace('/[^0-9]/', '', $request->harga);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $iconPath = $this->storageService->upload('icons/' . $fileName, $file);
        }

        $data = [
            'nama' => $request->nama,
            'harga' => $hargaClean,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'icon_path' => $iconPath,
            'created_at' => now()->toDateTimeString(),
        ];

        ObjekTarif::create($data);

        return redirect()->back()->with('success', 'Data berhasil disimpan ke Firebase!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'status' => 'required',
            'icon' => 'nullable|image|max:1024',
        ]);

        // Bersihkan format uang
        $hargaClean = (int) preg_replace('/[^0-9]/', '', $request->harga);

        $currentData = ObjekTarif::find($id);
        $iconPath = $currentData['icon_path'] ?? null;

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $iconPath = $this->storageService->upload('icons/' . $fileName, $file);
        }

        $data = [
            'nama' => $request->nama,
            'harga' => $hargaClean,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'icon_path' => $iconPath,
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
