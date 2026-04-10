<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Validator;

class PenetapanLokasiController extends Controller
{
    /**
     * Menampilkan halaman utama Penetapan Lokasi
     */
    public function index(Request $request)
    {
        $query = Lokasi::query();

        if ($request->has('search')) {
            $query->where('nama_lokasi', 'like', '%' . $request->search . '%');
        }

        $daftarLokasi = $query->orderBy('created_at', 'desc')->get();

        // Mengirim data ke view
        return view('penetapanlokasi', compact('daftarLokasi'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lokasi' => 'required|string|max:255',
            'koordinat'   => 'required|string',
            'target_harian' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        Lokasi::create([
            'nama_lokasi'   => $request->nama_lokasi,
            'koordinat'     => $request->koordinat,
            'target_harian' => $request->target_harian,
            'status'        => 'Aktif', 
        ]);

        return redirect()->route('penetapan-lokasi.index')
                         ->with('success', 'Lokasi berhasil ditetapkan!');
    }

    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return redirect()->route('penetapan-lokasi.index')
                         ->with('success', 'Lokasi berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->status = ($lokasi->status == 'Aktif') ? 'Inaktif' : 'Aktif';
        $lokasi->save();

        return redirect()->back();
    }
}