<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Login;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TikorController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\ObjekTarifController;

// Route::get('/', function () {
//     return view('welcome');
// });

//admin punya
Route::get('/dashboard-admin', [UserController::class, 'index']);
Route::get('/register', [UserController::class, 'create']);
Route::post('/test-store', [UserController::class, 'store']);
Route::get('/login-admin', [AdminController::class, 'loginadmin']);

//route login
Route::get('/dashboard-login', [LoginController::class, 'index']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/cek_login', [LoginController::class, 'cek_login']);


//operator punya
Route::get('/dashboard-operator', [OperatorController::class, 'index']);
Route::get('/dashboard-operator-penugasan', [OperatorController::class, 'penugasan']);
Route::get('/dashboard-operator-profile', [OperatorController::class, 'profile']);
Route::get('/dashboard-operator-survei', [OperatorController::class, 'survei']);
Route::post('/simpan-hitung-kendaraan', [OperatorController::class, 'simpanHitung'])->name('simpan.hitung.kendaraan');;


route::get('/dashboard', [TikorController::class, 'index']);
route::get('/dashboard-tikor', [TikorController::class, 'create']);
route::post('/update-lokasi-kantor', [TikorController::class, 'store']);


// Penugasan
route::get('/dashboard-penugasan', [PenugasanController::class, 'index']);
route::get('/dashboard-penugasan/create', [PenugasanController::class, 'create']);
route::post('/dashboard-penugasan/store', [PenugasanController::class, 'store']);
route::get('/dashboard-penugasan/{id}', [PenugasanController::class, 'show']);
route::get('/dashboard-penugasan/{id}/edit', [PenugasanController::class, 'edit']);
route::get('/dashboard-penugasan/{id}/update', [PenugasanController::class, 'update']);
route::get('/dashboard-penugasan/{id}/delete', [PenugasanController::class, 'destroy']);

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
   
});

//operator punya

Route::get('Objek_Tarif', [ObjekTarifController::class, 'index']);
