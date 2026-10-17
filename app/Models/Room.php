<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    // Mendaftarkan kolom mana saja yang boleh diisi datanya
    protected $fillable = [
        'code',
        'name',
        'building',
        'floor',
        'capacity',
        'qr_token',
        'is_active',
        'sensor_status',
        'sensor_payload',
        'sensor_updated_at',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'sensor_payload' => 'array',
        'sensor_updated_at' => 'datetime',
    ];
}
