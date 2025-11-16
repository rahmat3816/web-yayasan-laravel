<?php

namespace Database\Seeders;

use App\Models\Asrama;
use Illuminate\Database\Seeder;

class AsramaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Asrama Ibn Taimiyah', 'tipe' => 'putra', 'lokasi' => 'Komplek Putra 1'],
            ['nama' => 'Asrama Ibn Katsir', 'tipe' => 'putra', 'lokasi' => 'Komplek Putra 2'],
            ['nama' => 'Asrama Aisyah', 'tipe' => 'putri', 'lokasi' => 'Komplek Putri 1'],
            ['nama' => 'Asrama Fatimah', 'tipe' => 'putri', 'lokasi' => 'Komplek Putri 2'],
        ];

        foreach ($data as $item) {
            Asrama::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
