<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lokasi;

class LokasiSeeder extends Seeder
{
    public function run(): void
    {
        // Menghapus data lama agar tidak duplikat (opsional)
        Lokasi::truncate();

        $data = [
            [
                'nama_lokasi' => 'Jl. Diponegoro (Depan Pasar)',
                'koordinat' => '-8.5353, 115.4042',
                'target_harian' => 250000,
                'status' => 'Aktif'
            ],
            [
                'nama_lokasi' => 'Terminal Galiran',
                'koordinat' => '-8.5412, 115.4123',
                'target_harian' => 500000,
                'status' => 'Aktif'
            ],
            [
                'nama_lokasi' => 'Pelabuhan Kusamba',
                'koordinat' => '-8.5567, 115.4456',
                'target_harian' => 350000,
                'status' => 'Aktif'
            ],
            [
                'nama_lokasi' => 'Area Parkir Goa Lawah',
                'koordinat' => '-8.5512, 115.4678',
                'target_harian' => 400000,
                'status' => 'Aktif'
            ],
            [
                'nama_lokasi' => 'Jl. Puputan (Alun-alun)',
                'koordinat' => '-8.5378, 115.4056',
                'target_harian' => 150000,
                'status' => 'Inaktif'
            ],
            [
                'nama_lokasi' => 'Pasar Seni Semarapura',
                'koordinat' => '-8.5345, 115.4012',
                'target_harian' => 200000,
                'status' => 'Aktif'
            ],
        ];

        foreach ($data as $item) {
            Lokasi::create($item);
        }
    }
}