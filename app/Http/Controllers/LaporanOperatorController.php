<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanOperatorController extends Controller
{
    public function lapOperator()
    {
        return view('admin.laporan.lapOperator'); 
    }
}