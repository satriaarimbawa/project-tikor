<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\User;

class DaftarUserController extends Controller
{
   public function index()
{
    // Kita buat data manual supaya tabelnya tidak kosong
    $users = collect([
        (object)['username' => 'Budi', 'email' => 'budi@gmail.com', 'role' => 'Administrator'],
            (object)['username' => 'Roni', 'email' => 'roni@gmail.com', 'role' => 'Operator'],
            (object)['username' => 'Sasa', 'email' => 'sasa@gmail.com', 'role' => 'Operator'],
            (object)['username' => 'Rani', 'email' => 'rani@gmail.com', 'role' => 'Administrator'],
            (object)['username' => 'Mita', 'email' => 'mita@gmail.com', 'role' => 'Operator'],
            (object)['username' => 'Wawan', 'email' => 'wawan@gmail.com', 'role' => 'Operator'],
    ]);

    return view('admin.register.daftar-user', compact('users'));
}
}