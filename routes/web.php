<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Login;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TikorController;
use App\Http\Controllers\PenugasanController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard-admin', [UserController::class, 'index']);
Route::get('/register', [UserController::class, 'create']);
Route::post('/test-store', [UserController::class, 'store']);
Route::get('/login-admin', [AdminController::class, 'loginadmin']);

Route::get('/dashboard-login', [LoginController::class, 'index']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/cek_login', [LoginController::class, 'cek_login']);



Route::get('/dashboard-operator', [OperatorController::class, 'index']);
Route::get('/dashboard-operator-penugasan', [OperatorController::class, 'penugasan']);


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

Route::get('/', function () {
    return view('landing');
});