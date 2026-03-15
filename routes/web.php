<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Auth\Events\Login;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-store', [UserController::class, 'store']);
Route::get('/dashboard-admin', [AdminController::class, 'index']);
Route::get('/dashboard-login', [LoginController::class, 'index']);
Route::get('/dashboard-operator', [OperatorController::class, 'index']);

Route::get('/', function () {
    return view('landing');
});