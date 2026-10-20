<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms/live', [DashboardController::class, 'live'])->name('rooms.live');

    // TIDAK ADA ENDPOINT BOOKING. Aksi scan POST langsung check-in / check-out.
    Route::get('/scan', [DashboardController::class, 'scanner'])->name('scanner');
    Route::post('/scan', [RoomSessionController::class, 'scan'])->middleware('throttle:12,1')->name('scan.process');
    Route::post('/scan/checkin', [RoomSessionController::class, 'storeCheckin'])
        ->middleware('throttle:12,1')
        ->name('scan.checkin');
    Route::get('/scan/hasil/{code}', [DashboardController::class, 'scanResult'])->name('scan.result');

    Route::get('/riwayat', [DashboardController::class, 'history'])->name('reservation.history');
    Route::get('/room/{code}/detail-modal', [DashboardController::class, 'getRoomModal'])->name('room.detail-modal');
    Route::get('/admin/qr-ruangan', [DashboardController::class, 'qrLabels'])->name('rooms.qr-labels');
});
