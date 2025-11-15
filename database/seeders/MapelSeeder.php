<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mapel;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        $mapelList = [
            ['nama' => 'Fiqih', 'tipe' => 'syari'],
            ['nama' => 'Aqidah', 'tipe' => 'syari'],
            ['nama' => 'Tahfizh', 'tipe' => 'syari'],
            ['nama' => 'Hadits', 'tipe' => 'syari'],
            ['nama' => 'Tafsir', 'tipe' => 'syari'],
            ['nama' => 'Akhlak', 'tipe' => 'syari'],
            ['nama' => 'Matematika', 'tipe' => 'umum'],
            ['nama' => 'Bahasa Inggris', 'tipe' => 'umum'],
            ['nama' => 'Bahasa Indonesia', 'tipe' => 'umum'],
            ['nama' => 'IPA', 'tipe' => 'umum'],
            ['nama' => 'IPS', 'tipe' => 'umum'],
            ['nama' => 'Penjaskes', 'tipe' => 'umum'],
        ];

        foreach ($mapelList as $mapel) {
            Mapel::firstOrCreate([
                'nama' => $mapel['nama'],
                'tipe' => $mapel['tipe'],
            ]);
        }
    }
}
