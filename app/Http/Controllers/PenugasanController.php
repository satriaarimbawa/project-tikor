<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;


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

    $tugas = $this->database->getReference('penugasan')->getValue() ?? [];
    $users = $this->database->getReference('users')->getValue() ?? [];
    $lokasitikor = $this->database->getReference('pengaturan_lokasi')->getValue() ?? [];
 
    $dataFinal = [];

    foreach ($tugas as $idPenugasan => $data) {
        $uid = $data['id_user'] ?? null;
        $idLokasi = $data['id_lokasi'] ?? null;

    $rawMulai = $data['waktu_mulai'];
    $rawSelesai = $data['waktu_selesai'];
    $tglMulai = \Carbon\Carbon::parse($rawMulai)->locale('id')->translatedFormat('d F Y');
    $tglSelesai = \Carbon\Carbon::parse($rawSelesai)->locale('id')->translatedFormat('d F Y');
    
    $jamMulai = \Carbon\Carbon::parse($rawMulai)->format('H:i');
    $jamSelesai = \Carbon\Carbon::parse($rawSelesai)->format('H:i');

        $dataFinal[] = [
            'id' => $idPenugasan,
            'nama_operator' => $users[$uid]['username'] ?? 'User Tidak Ditemukan',
            'alamat_lokasi' => $lokasitikor[$idLokasi]['alamat'] ?? 'Lokasi Tidak Ditemukan',
            'waktu_mulai'   => $data['waktu_mulai'] ?? '-',
            'waktu_selesai' => $data['waktu_selesai'] ?? '-',
            'no_spt'        => $data['surat_spt'] ?? '-',
            'tanggal_rentang' => $tglMulai . ' s/d ' . $tglSelesai,
            'jam_rentang' => $jamMulai . ' - ' . $jamSelesai . ' WITA',
        ];
    }


    // @dd($dataFinal); 

            return view('admin.penugasan.index',
            [
                'dataPenugasan' => $dataFinal
            ]);

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
        $request->validate([
            'id_user' => 'required',
            'id_lokasi' => 'required',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'surat_spt' => 'required|file|mimes:pdf,jpg,png|max:2048', // Max 2MB
        ]);

        try {
            $file = $request->file('surat_spt');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/spt'), $namaFile);

        
            $mulai = Carbon::parse($request->waktu_mulai)->format('Y-m-d H:i:s');
            $selesai = Carbon::parse($request->waktu_selesai)->format('Y-m-d H:i:s');

            
            $dataPenugasan = [
                'id_user'       => $request->id_user,
                'id_lokasi'     => $request->id_lokasi,
                'waktu_mulai'   => $mulai,
                'waktu_selesai' => $selesai,
                'file_spt'      => $namaFile,
                'objek_survei'  => $request->objek_terpilih, // Diambil dari hidden input JS Anda
                'keterangan'    => $request->keterangan ?? '-',
                'created_at'    => Carbon::now('Asia/Makassar')->format('Y-m-d H:i:s'),
            ];

            $this->database->getReference('penugasan')->push($dataPenugasan);

            return redirect()->to('dashboard-penugasan')->with('success', 'Penugasan berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
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