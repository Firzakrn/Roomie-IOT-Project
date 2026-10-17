<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/scan/{token}', [DashboardController::class, 'handleScan'])->name('scan.handle');
Route::post('/reservasi', [DashboardController::class, 'storeReservation'])->name('reservation.store');
Route::get('/room/{code}/detail-modal', [DashboardController::class, 'getRoomModal'])->name('room.detail-modal');