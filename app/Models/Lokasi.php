<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';

    protected $fillable = [
        'nama_lokasi',
        'koordinat',
        'target_harian',
        'status',
    ];

    // Opsional: Memastikan data dibaca sebagai angka
    protected $casts = [
        'target_harian' => 'integer',
    ];

    // Opsional: Memberikan nilai default "Aktif" jika tidak diisi
    protected $attributes = [
        'status' => 'Aktif',
    ];
}