<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Aplikasi (wajib login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard')->name('home');

    // Super Admin dan Admin: Dashboard, OPD, Domain, Pemetaan, Laporan.
    Route::middleware('role:'.User::ROLE_SUPER_ADMIN.','.User::ROLE_ADMIN)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');

        Route::get('/pemetaan', [PemetaanController::class, 'index'])->name('pemetaan.index');
        Route::get('/pemetaan/export', [PemetaanController::class, 'export'])->name('pemetaan.export');
        Route::get('/pemetaan/{opd}', [PemetaanController::class, 'show'])->name('pemetaan.show');
        Route::get('/pemetaan/{opd}/export', [PemetaanController::class, 'exportDetail'])->name('pemetaan.export-detail');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        Route::resource('opd', OpdController::class)->except('show');
        Route::resource('domain', DomainController::class);
    });

    // Khusus Super Admin: Manajemen User. Admin yang membuka URL ini mendapat 403.
    Route::middleware('role:'.User::ROLE_SUPER_ADMIN)->group(function () {
        Route::resource('user', UserController::class)->except('show');
    });
});
