<?php

namespace App\Http\Controllers;

use App\Models\FirebaseUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Menyimpan user baru ke Firebase
$nama  = $request->query('nama', 'Satria Default');
    $nim   = $request->query('nim', '230030180');
    $kelas = $request->query('kelas', 'CB233'); 
    $password = $request->query('password', '123456');

    $userId = FirebaseUser::create([
        'username'  => $nama,
        'email'   => $nim,
        'role_user' => $kelas,
        'password'=> $password
    ]);

    return response()->json([
        'message' => 'User berhasil disimpan ke Firebase',
        'id'      => $userId,
        'data'    => ['nama' => $nama, 'nim' => $nim]
    ]);
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
