<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjekTarifController extends Controller
{
    public function index() 
    {
        // Data dummy untuk memastikan tampilan 100% sesuai desain
        $data = [
            ['nama' => 'SPD-Motor', 'harga' => 2000, 'status' => 'Aktif'],
            ['nama' => 'Mini Bus', 'harga' => 5000, 'status' => 'Inaktif'],
            ['nama' => 'Bus', 'harga' => 10000, 'status' => 'Aktif'],
            ['nama' => 'Truk Sedang', 'harga' => 15000, 'status' => 'Inaktif'],
            ['nama' => 'Truk Besar', 'harga' => 25000, 'status' => 'Aktif'],
            ['nama' => 'Alat Berat', 'harga' => 30000, 'status' => 'Inaktif'],
        ];

        // Mengirim data ke resources/views/Objek_Tarif.blade.php
        return view('admin.Objek_Tarif', compact('data')); 
    }
}