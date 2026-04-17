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
use App\Http\Controllers\LaporanLokasiController;
use App\Http\Controllers\LaporanOperatorController; 


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
    Route::get('/dashboard-penugasan/create', [PenugasanController::class, 'create']);
    Route::post('/dashboard-penugasan/store', [PenugasanController::class, 'store']);
    Route::get('/dashboard-penugasan/edit/{id}', [PenugasanController::class, 'edit']);
    Route::post('/dashboard-penugasan/update/{id}', [PenugasanController::class, 'update']);
    Route::delete('/delete-penugasan/{id}', [PenugasanController::class, 'destroy']);

    //penetapan titik koordinat uji
    Route::get('/dashboard-tikor', [TikorController::class, 'index'])->name('penetapan-lokasi.index');
    Route::post('/update-lokasi-tikor', [TikorController::class, 'store'])->name('penetapan-lokasi.store');
    Route::delete('/delete-lokasi-tikor/{id}', [TikorController::class, 'destroy'])->name('penetapan-lokasi.destroy');

    // Objek & Tarif
    Route::prefix('Objek_Tarif')->group(function () {
        Route::get('/', [ObjekTarifController::class, 'index'])->name('objek-tarif.index');
        Route::post('/store', [ObjekTarifController::class, 'store'])->name('objek-tarif.store');
        Route::put('/update/{id}', [ObjekTarifController::class, 'update'])->name('objek-tarif.update');
        Route::delete('/delete/{id}', [ObjekTarifController::class, 'destroy'])->name('objek-tarif.destroy');
    });

    // Manajemen User
    Route::get('daftar-user', [DaftarUserController::class, 'index']);
    Route::get('/tambahuser', [TambahUserController::class, 'create'])->name('user.create');
    Route::post('/user/simpan', [TambahUserController::class, 'store'])->name('user.store');

    // Laporan Lokasi
    Route::get('/laporan_lokasi', [LaporanLokasiController::class, 'index'])->name('laporan.lokasi');
    Route::post('/laporan_lokasi/filter', [LaporanLokasiController::class, 'filter'])->name('laporan.lokasi.filter');
    Route::get('/laporan_lokasi/download', [LaporanLokasiController::class, 'downloadPdf'])->name('laporan.lokasi.download');

    // Laporan Operator (Fitur dari teman)
    Route::get('/lapOperator', [LaporanOperatorController::class, 'lapOperator']);
});


Route::middleware(['operator'])->group(function () {
    Route::get('/dashboard-operator', [OperatorController::class, 'index']);
    Route::get('/dashboard-operator-penugasan', [OperatorController::class, 'penugasan']);
    Route::get('/dashboard-operator-survei', [OperatorController::class, 'survei']);
    Route::post('/simpan-hitung-kendaraan', [OperatorController::class, 'simpanHitung'])->name('simpan.hitung.kendaraan');
    Route::get('/dashboard-operator-profile', [OperatorController::class, 'profile']);
    Route::post('/check-location-radius', [LoginController::class, 'checkLocationRadius'])->name('check.location.radius');
});
