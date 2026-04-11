<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanLokasiController extends Controller
{
    public function index()
    {
        return view('laporan_lokasi'); 
    }

    public function filter(Request $request)
    {
        // Logika filter data
    }

    public function downloadPdf()
    {
        // Logika download PDF
    }
}