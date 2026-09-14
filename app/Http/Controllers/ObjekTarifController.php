<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ObjekTarif;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Str;

class ObjekTarifController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $firebaseData = ObjekTarif::all() ?? [];
        
        $data = [];
        foreach ($firebaseData as $id => $item) {
            $iconUrl = (isset($item['icon_path']) && $item['icon_path']) 
                ? $this->storageService->getPublicUrl($item['icon_path']) 
                : asset('assets/logo_dishub.png');
                
            $data[] = [
                'id' => $id,
                'nama' => $item['nama'] ?? '-',
                'harga' => $item['harga'] ?? 0,
                'tarif_lama' => $item['tarif_lama'] ?? 0,
                'status' => $item['status'] ?? 'Inaktif',
                'keterangan' => $item['keterangan'] ?? '',
                'icon_url' => $iconUrl
            ];
        }

        // Pagination Manual
        $perPage = (int) $request->input('perPage', 5);
        $currentPage = (int) $request->input('page', 1);
        $totalData = count($data);
        $totalPages = ceil($totalData / $perPage);
        $offset = ($currentPage - 1) * $perPage;
        
        $dataPaginated = array_slice($data, $offset, $perPage);

        return view('admin.Objek_Tarif', [
            'data' => $dataPaginated,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'tarif_lama' => 'required',
            'status' => 'required',
            'icon' => 'nullable|image|max:1024',
        ]);

        // Bersihkan format uang (titik) menjadi angka murni
        $hargaClean = (int) preg_replace('/[^0-9]/', '', $request->harga);
        $tarifLamaClean = (int) preg_replace('/[^0-9]/', '', $request->tarif_lama);

        $iconPath = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '_' . Str::random(16) . '.' . ($ext ?: 'png');
            $iconPath = $this->storageService->upload('icons/' . $fileName, $file);
        }

        $data = [
            'nama' => $request->nama,
            'harga' => $hargaClean,
            'tarif_lama' => $tarifLamaClean,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'icon_path' => $iconPath,
            'created_at' => now()->toDateTimeString(),
        ];

        // Cek duplikasi nama
        $existingData = ObjekTarif::all() ?? [];
        $newNama = strtolower($request->nama);
        foreach ($existingData as $item) {
            if (strtolower($item['nama'] ?? '') === $newNama) {
                return redirect()->back()
                    ->with('duplicate', 'Objek dengan nama "' . $request->nama . '" sudah ada dalam database.')
                    ->withInput();
            }
        }

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
        $tarifLama = $currentData['tarif_lama'] ?? 0;
        $hargaLama = $currentData['harga'] ?? 0;

        // Jika harga berubah, maka harga lama masuk ke tarif_lama
        if ($hargaClean != $hargaLama) {
            $tarifLama = $hargaLama;
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . '_' . Str::random(16) . '.' . ($ext ?: 'png');
            $iconPath = $this->storageService->upload('icons/' . $fileName, $file);
        }

        $data = [
            'nama' => $request->nama,
            'harga' => $hargaClean,
            'tarif_lama' => $tarifLama,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? '',
            'icon_path' => $iconPath,
            'updated_at' => now()->toDateTimeString(),
        ];

        // Cek duplikasi nama (kecuali data yang sedang diedit)
        $existingData = ObjekTarif::all() ?? [];
        $newNama = strtolower($request->nama);
        foreach ($existingData as $itemId => $item) {
            if ($itemId === $id) continue;
            if (strtolower($item['nama'] ?? '') === $newNama) {
                return redirect()->back()
                    ->with('duplicate', 'Nama objek "' . $request->nama . '" sudah digunakan oleh data lain.')
                    ->withInput();
            }
        }

        ObjekTarif::update($id, $data);

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy($id)
    {
        ObjekTarif::delete($id);
        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }
}
