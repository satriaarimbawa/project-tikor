<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TikorController;
use App\Http\Controllers\PenugasanController;


//link landing page 
Route::get('/', function () {
    return view('landing');
});

//route login
Route::get('/login', [LoginController::class, 'index']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/cek_login', [LoginController::class, 'cek_login']);
Route::get('/login-admin', [AdminController::class, 'loginadmin']);



Route::middleware(['admin'])->group(function () {

    //dashboard admin
    Route::get('/dashboard-admin', [UserController::class, 'index']);
    Route::get('/register', [UserController::class, 'create']);
    Route::post('/test-store', [UserController::class, 'store']);

    //penugasan operator
    route::get('/dashboard-penugasan', [PenugasanController::class, 'index']);
    route::get('/dashboard-penugasan/create', [PenugasanController::class, 'create']);
    route::post('/dashboard-penugasan/store', [PenugasanController::class, 'store']);

    //penetapan titik koordinat uji
    route::get('/dashboard', [TikorController::class, 'index']);
    route::get('/dashboard-tikor', [TikorController::class, 'create']);
    route::post('/update-lokasi-kantor', [TikorController::class, 'store']);
});



Route::middleware(['operator'])->group(function () {
    Route::get('/dashboard-operator', [OperatorController::class, 'index']);
    Route::get('/dashboard-operator-penugasan', [OperatorController::class, 'penugasan']);
    Route::get('/dashboard-operator-survei', [OperatorController::class, 'survei']);
    Route::post('/simpan-hitung-kendaraan', [OperatorController::class, 'simpanHitung'])->name('simpan.hitung.kendaraan');
    Route::get('/dashboard-operator-profile', [OperatorController::class, 'profile']);
});

//operator punya


