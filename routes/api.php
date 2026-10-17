<?php

use App\Http\Controllers\Api\RoomDeviceController;
use Illuminate\Support\Facades\Route;

// Endpoint IoT menggunakan header X-Device-Key, bukan sesi login/browser.
Route::get('/room/{code}/status', [RoomDeviceController::class, 'checkStatus']);
Route::post('/room/{code}/sensor', [RoomDeviceController::class, 'updateSensor']);
