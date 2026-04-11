<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TikorController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\ObjekTarifController;
use App\Http\Controllers\DaftarUserController;
use App\Http\Controllers\TambahUserController;
use App\Http\Controllers\PenetapanLokasiController;
use App\Http\Controllers\LaporanLokasiController;


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
    Route::get('/dashboard-penugasan', [PenugasanController::class, 'index']);
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
    Route::get('/dashboard-operator-survei', [OperatorController::class, 'survei']);
    Route::post('/simpan-hitung-kendaraan', [OperatorController::class, 'simpanHitung'])->name('simpan.hitung.kendaraan');
    Route::get('/dashboard-operator-profile', [OperatorController::class, 'profile']);
    Route::post('/check-location-radius', [LoginController::class, 'checkLocationRadius'])->name('check.location.radius');
});

//operator punya

Route::get('Objek_Tarif', [ObjekTarifController::class, 'index']);
Route::get('daftar-user', [DaftarUserController::class, 'index']);
Route::get('/tambahuser', [TambahUserController::class, 'create'])->name('user.create');
Route::post('/user/simpan', [TambahUserController::class, 'store'])->name('user.store');

Route::get('/penetapanlokasi', [PenetapanLokasiController::class, 'index'])->name('penetapan-lokasi.index');

Route::post('/penetapanlokasi', [PenetapanLokasiController::class, 'store'])->name('penetapan-lokasi.store');

Route::delete('/penetapanlokasi/{id}', [PenetapanLokasiController::class, 'destroy'])->name('penetapan-lokasi.destroy');

Route::get('/laporan_lokasi', [LaporanLokasiController::class, 'index'])->name('laporan.lokasi');

Route::post('/laporan_lokasi/filter', [LaporanLokasiController::class, 'filter'])->name('laporan.lokasi.filter');
Route::get('/laporan_lokasi/download', [LaporanLokasiController::class, 'downloadPdf'])->name('laporan.lokasi.download');