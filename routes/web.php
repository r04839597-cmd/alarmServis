<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModificationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// ---------- Guest ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

// ---------- Authenticated ----------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => redirect()->to(auth()->user()->getRedirectRoute()))->name('dashboard');

    Route::middleware('role:superadmin')
        ->prefix('superadmin')->name('superadmin.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');
        });

    Route::middleware('role:admin_bengkel')
        ->prefix('admin-bengkel')->name('admin_bengkel.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'adminBengkel'])->name('dashboard');
        });

    Route::middleware('role:user')
        ->prefix('user')->name('user.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'user'])->name('dashboard');

            Route::get('/motor', [VehicleController::class, 'index'])->name('vehicles.index');
            Route::get('/motor/tambah', [VehicleController::class, 'create'])->name('vehicles.create');
            Route::post('/motor', [VehicleController::class, 'store'])->name('vehicles.store');
            Route::get('/motor/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
            Route::post('/motor/{vehicle}/odometer', [VehicleController::class, 'updateOdometer'])->name('vehicles.odometer.update');

            Route::post('/motor/{vehicle}/servis', [ServiceController::class, 'store'])->name('vehicles.service.store');
            Route::get('/motor/{vehicle}/riwayat-servis', [ServiceController::class, 'index'])->name('vehicles.service.index');

            Route::get('/motor/{vehicle}/modifikasi', [ModificationController::class, 'edit'])->name('vehicles.modifications.edit');
            Route::post('/motor/{vehicle}/modifikasi', [ModificationController::class, 'update'])->name('vehicles.modifications.update');
            Route::get('/motor/{vehicle}/riwayat-modifikasi', [ModificationController::class, 'history'])->name('vehicles.modifications.history');
        });
});
