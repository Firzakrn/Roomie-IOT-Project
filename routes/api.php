<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomDeviceController;

// Endpoint untuk ESP32 Wokwi
Route::get('/room/{code}/status', [RoomDeviceController::class, 'checkStatus']);
Route::post('/room/{code}/sensor', [RoomDeviceController::class, 'updateSensor']);