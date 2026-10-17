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
    ];
}