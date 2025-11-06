<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->truncate();

        $units = [
            ['nama_unit' => "TK Permata Sunnah"],
            ['nama_unit' => "MI Imam Syafi'i"],
            ['nama_unit' => "MTS Imam Syafi'i"],
            ['nama_unit' => "MTS As-Sunnah Gorontalo"],
            ['nama_unit' => "MA As-Sunnah Limboto Barat"],
            ['nama_unit' => "Pondok Pesantren As-Sunnah Gorontalo"],
            ['nama_unit' => "Pondok Pesantren UMA Gorontalo"],
        ];

        DB::table('units')->insert($units);
    }
}
