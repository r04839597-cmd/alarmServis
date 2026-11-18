<?php

use App\Http\Controllers\AdminBengkel\CatalogController;
use App\Http\Controllers\AdminBengkel\PhotoController;
use App\Http\Controllers\AdminBengkel\WorkshopController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModificationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Superadmin\ModerationController;
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

    // ----- Superadmin -----
    Route::middleware('role:superadmin')
        ->prefix('superadmin')->name('superadmin.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');

            Route::get('/bengkel', [ModerationController::class, 'workshops'])->name('workshops.index');
            Route::post('/bengkel/{workshop}/setujui', [ModerationController::class, 'approveWorkshop'])->name('workshops.approve');
            Route::post('/bengkel/{workshop}/tolak', [ModerationController::class, 'rejectWorkshop'])->name('workshops.reject');

            Route::get('/kuota', [ModerationController::class, 'quotaRequests'])->name('quota.index');
            Route::post('/kuota/{quotaRequest}/setujui', [ModerationController::class, 'approveQuota'])->name('quota.approve');
            Route::post('/kuota/{quotaRequest}/tolak', [ModerationController::class, 'rejectQuota'])->name('quota.reject');
        });

    // ----- Admin Bengkel -----
    Route::middleware('role:admin_bengkel')
        ->prefix('admin-bengkel')->name('admin_bengkel.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'adminBengkel'])->name('dashboard');

            Route::get('/bengkel', [WorkshopController::class, 'index'])->name('workshops.index');
            Route::get('/bengkel/tambah', [WorkshopController::class, 'create'])->name('workshops.create');
            Route::post('/bengkel', [WorkshopController::class, 'store'])->name('workshops.store');
            Route::get('/bengkel/{workshop}/ubah', [WorkshopController::class, 'edit'])->name('workshops.edit');
            Route::put('/bengkel/{workshop}', [WorkshopController::class, 'update'])->name('workshops.update');
            Route::delete('/bengkel/{workshop}', [WorkshopController::class, 'destroy'])->name('workshops.destroy');

            Route::post('/kuota/ajukan', [WorkshopController::class, 'requestQuota'])->name('quota.request');

            Route::get('/bengkel/{workshop}/katalog', [CatalogController::class, 'index'])->name('catalog.index');
            Route::post('/bengkel/{workshop}/katalog', [CatalogController::class, 'store'])->name('catalog.store');
            Route::get('/bengkel/{workshop}/katalog/{catalog}/ubah', [CatalogController::class, 'edit'])->name('catalog.edit');
            Route::put('/bengkel/{workshop}/katalog/{catalog}', [CatalogController::class, 'update'])->name('catalog.update');
            Route::delete('/bengkel/{workshop}/katalog/{catalog}', [CatalogController::class, 'destroy'])->name('catalog.destroy');

            Route::get('/bengkel/{workshop}/foto', [PhotoController::class, 'index'])->name('photos.index');
            Route::post('/bengkel/{workshop}/foto', [PhotoController::class, 'store'])->name('photos.store');
            Route::delete('/bengkel/{workshop}/foto/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
        });

    // ----- User (Pemilik Motor) -----
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
