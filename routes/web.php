<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\TikorController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\ObjekTarifController;
use App\Http\Controllers\DaftarUserController;
use App\Http\Controllers\LaporanLokasiController;
use App\Http\Controllers\LaporanOperatorController; 
use App\Http\Controllers\LiveDashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TelegramWebhookController;

// Webhook Bot Telegram
Route::post('/api/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

//link landing page 
Route::get('/', function () {
    return view('landing');
});

//route login
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::get('/logout', [LoginController::class, 'logout']);
Route::post('/cek_login', [LoginController::class, 'cek_login']);
Route::get('/login-admin', [AdminController::class, 'loginadmin']);

// Live Monitoring Dashboard (Public Command Center)
Route::get('/dashboard-live', [LiveDashboardController::class, 'index'])->name('admin.live');

// Route Lupa Password

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCodeEmail'])->name('password.email');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

if (app()->environment('local')) {
    Route::get('/_test_auth/{role}', function ($role) {
        $isItSupport = ($role === 'it_support');
        session()->put([
            'login_status' => true,
            'username'     => $isItSupport ? 'IT Support Satria' : 'Admin Operasional',
            'email'        => $isItSupport ? 'itsupport@dishub.go.id' : 'admin@dishub.go.id',
            'role'         => $role,
            'is_it_support' => $isItSupport,
            'user_id'      => 'test_' . $role,
            'isLoggedIn'   => true,
        ]);
        return redirect('/dashboard-admin');
    });
}


Route::middleware(['admin'])->group(function () {

    //dashboard admin
    Route::get('/dashboard-admin', [AdminController::class, 'index']);
    Route::get('/register', [DaftarUserController::class, 'create']);
    Route::post('/test-store', [DaftarUserController::class, 'store']);

    //penugasan operator
    Route::get('/dashboard-penugasan', [PenugasanController::class, 'index']);
    Route::get('/dashboard-penugasan/create', [PenugasanController::class, 'create']);
    Route::post('/dashboard-penugasan/store', [PenugasanController::class, 'store']);
    Route::get('/dashboard-penugasan/edit/{id}', [PenugasanController::class, 'edit']);
    Route::post('/dashboard-penugasan/update/{id}', [PenugasanController::class, 'update']);
    Route::post('/dashboard-penugasan/reset/{id}', [PenugasanController::class, 'resetStatus'])->name('penugasan.reset');
    Route::delete('/delete-penugasan/{id}', [PenugasanController::class, 'destroy']);

    // Notifikasi
    Route::get('/api/notifications', [AdminController::class, 'getNotifications']);
    Route::post('/api/notifications/mark-read', [AdminController::class, 'markNotificationsRead']);


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
    Route::get('/tambahuser', [DaftarUserController::class, 'create'])->name('user.create');
    Route::post('/user/simpan', [DaftarUserController::class, 'store'])->name('user.store');
    Route::get('/user/edit/{id}', [DaftarUserController::class, 'edit'])->name('user.edit');
    Route::post('/user/update/{id}', [DaftarUserController::class, 'update'])->name('user.update');
    Route::delete('/user/hapus/{id}', [DaftarUserController::class, 'destroy'])->name('user.destroy');

    // Laporan Lokasi
    Route::get('/laporan_lokasi', [LaporanLokasiController::class, 'index'])->name('laporan.lokasi');
    Route::post('/laporan_lokasi/filter', [LaporanLokasiController::class, 'filter'])->name('laporan.lokasi.filter');
    Route::get('/laporan_lokasi/download', [LaporanLokasiController::class, 'downloadPdf'])->name('laporan.lokasi.download');

    // Laporan Operator (Fitur dari teman)
    Route::get('/lapOperator', [LaporanOperatorController::class, 'lapOperator'])->name('laporan.operator');
    Route::get('/lapOperator/download', [LaporanOperatorController::class, 'downloadPdf'])->name('laporan.operator.download');

    // Log Aktivitas
    Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('admin.activity-log');
    Route::get('/log-aktivitas/download', [ActivityLogController::class, 'downloadPdf'])->name('admin.activity-log.download');

    // Pengaturan Sistem (Eksklusif IT Support)
    Route::middleware(['it_support'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/radius', [SettingController::class, 'updateRadius'])->name('settings.radius.update');
        Route::post('/settings/session-security', [SettingController::class, 'updateSessionSecurity'])->name('settings.session.update');
        Route::post('/settings/telegram', [SettingController::class, 'updateTelegram'])->name('settings.telegram.update');
        Route::post('/settings/telegram-test', [SettingController::class, 'testTelegram'])->name('settings.telegram.test');
        Route::get('/settings/health-check', [SettingController::class, 'runHealthCheck'])->name('settings.health.check');
        Route::post('/settings/operational', [SettingController::class, 'updateOperational'])->name('settings.operational.update');
        Route::get('/settings/backup-download', [SettingController::class, 'downloadBackup'])->name('settings.backup.download');
    });
});


Route::middleware(['operator'])->group(function () {
    Route::get('/dashboard-operator', [OperatorController::class, 'index']);
    Route::get('/dashboard-operator-penugasan', [OperatorController::class, 'penugasan']);
    Route::get('/dashboard-operator-survei', [OperatorController::class, 'survei']);
    Route::post('/simpan-hitung-kendaraan', [OperatorController::class, 'simpanHitung'])->name('simpan.hitung.kendaraan');
    Route::post('/lapor-survei', [OperatorController::class, 'laporSurvei'])->name('lapor.survei');
    Route::post('/toggle-istirahat', [OperatorController::class, 'toggleIstirahat'])->name('operator.toggle-istirahat');
    Route::post('/claim-tugas', [OperatorController::class, 'claimTugas'])->name('operator.claim-tugas');
    Route::get('/dashboard-operator-profile', [OperatorController::class, 'profile']);
    Route::post('/check-location-radius', [LoginController::class, 'checkLocationRadius'])->name('check.location.radius');
    Route::get('/dashboard-operator-download-pdf', [OperatorController::class, 'downloadPdf'])->name('operator.download.pdf');
});
