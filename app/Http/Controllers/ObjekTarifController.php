<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ObjekTarif;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Str;
use App\Support\ObjekKunci;

/**
 * CRUD master objek tarif kendaraan.
 *
 * Dua hal penting di file ini:
 *
 *   1. `nama` adalah LABEL yang bebas diganti admin.
 *   2. `kunci` adalah IDENTITAS DATA yang permanen, dibekukan saat objek
 *      dibuat lewat `ObjekKunci::dariNama()` dan TIDAK PERNAH dihitung ulang
 *      saat objek di-rename.
 *
 * Pemisahan inilah yang memperbaiki bug "sebagian data survei tidak muncul":
 * seluruh laporan mencocokkan tarif lewat `kunci`, bukan lewat `nama`.
 * Rinciannya ada di `App\Support\ObjekKunci`.
 */
class ObjekTarifController extends Controller
{
    /** Layanan upload ikon ke Firebase Storage. */
    protected $storageService;

    /**
     * @param FirebaseStorageService $storageService Injeksi dari container Laravel.
     */
    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Daftar objek tarif dengan pagination manual.
     *
     * Ikon diambil dari Firebase Storage; objek tanpa ikon memakai logo
     * bawaan aplikasi.
     *
     * @param Request $request Mengandung `perPage` (default 5) dan `page` (default 1).
     * @return \Illuminate\View\View
     */
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

    /**
     * Simpan objek tarif baru.
     *
     * Field `kunci` digenerate otomatis di sini dari nama saat pembuatan.
     * Admin tidak pernah mengetiknya, dan tidak ada langkah manual tambahan
     * untuk objek baru.
     *
     * @param Request $request Mengandung `nama`, `harga`, `tarif_lama`,
     *        `status`, opsional `keterangan` dan `icon`.
     * @return \Illuminate\Http\RedirectResponse
     */
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

            // `kunci` = identitas data yang PERMANEN, dibekukan saat objek
            // dibuat. Wajib di-generate otomatis di sini: admin tidak pernah
            // mengetiknya, dan tidak perlu tindakan manual untuk objek baru.
            //
            // Mulai objek ini, nama objek boleh diganti-ubah admin tanpa
            // memutus riwayat data survei.
            'kunci' => ObjekKunci::dariNama((string)$request->nama),
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

    /**
     * Perbarui objek tarif yang sudah ada.
     *
     * `kunci` TIDAK dihitung ulang, apesar `nama` boleh berubah. Inilah
     * penjaga agar me-rename objek tidak memutus riwayat surveinya.
     * `ObjekTarif::update()` memakai operasi Firebase merge, jadi `kunci`
     * lama sudah tertahan; di sini ditulis eksplisit sebagai pengaman
     * tambahan.
     *
     * `tarif_lama` otomatis mengikuti harga lama bila harga berubah.
     *
     * @param Request $request Mengandung `nama`, `harga`, `status`,
     *        opsional `keterangan` dan `icon`.
     * @param string  $id      ID node objek tarif di `objek_tarif`.
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // `kunci` SENGAJA TIDAK dihitung ulang di sini. Ini identitas data
        // historis objek tersebut; kalau ikut berubah saat admin me-rename,
        // seluruh riwayat survei untuk objek ini akan lenyap dari laporan
        // persis seperti bug lama. ObjekTarif::update() memakai Firebase
        // merge sehingga `kunci` lama sudah otomatis tertahan; baris berikut
        // hanya menjadikannya eksplisit, aman walau Model::update() nanti
        // berubah dari merge menjadi set().
        if (!empty($currentData['kunci'])) {
            $data['kunci'] = $currentData['kunci'];
        }

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

    /**
     * Hapus objek tarif beserta ikonnya.
     *
     * Riwayat `survei_harian` yang sudah tertulis TIDAK ikut terhapus, jadi
     * penghapusan objek akan membuat datanya tidak lagi punya kolom tarif
     * di laporan. Laporan menandai ini di blok peringatan, bukan
     * menghapusnya.
     *
     * @param string $id ID node objek tarif di `objek_tarif`.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        ObjekTarif::delete($id);
        return redirect()->back()->with('success', 'Data berhasil dihapus!');
    }
}
