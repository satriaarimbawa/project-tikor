<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-store', [UserController::class, 'store']);
Route::get('/dashboard-admin', [AdminController::class, 'index']);
