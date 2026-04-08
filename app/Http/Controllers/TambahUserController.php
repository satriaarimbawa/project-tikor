<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Support\Facades\Hash; 

class TambahUserController extends Controller
{
    public function create()
    {
        return view('tambahuser'); 
    }

    public function store(Request $request)
    {
        // 1. Tambahkan validasi untuk id_user agar tidak duplikat
        $request->validate([
            'id_user'    => 'required|unique:users,id_user', // Pastikan id_user unik di database
            'username'   => 'required|unique:users,username',
            'email'      => 'required|email|unique:users,email',
            'level_user' => 'required',
            'password'   => 'required|min:6',
        ]);

        // 2. Tambahkan id_user ke dalam proses pembuatan user
        User::create([
            'id_user'    => $request->id_user, // Simpan ID yang diketik manual
            'username'   => $request->username,
            'email'      => $request->email,
            'level_user' => $request->level_user,
            'password'   => Hash::make($request->password), 
        ]);

        return redirect()->route('user.create')->with('success', 'User berhasil ditambahkan!');
    }
}