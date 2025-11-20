<?php

namespace Database\Seeders;

use App\Models\Mutun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MutunSeeder extends Seeder
{
    public function run(): void
    {
        $kitabs = [
            [
                'name' => "Mutun Tholabul 'Ilmi - Tamhidi",
                'start' => 11,
                'end' => 91,
            ],
            [
                'name' => "Mutun Tholabul 'Ilmi - Awal",
                'start' => 25,
                'end' => 109,
            ],
        ];

        DB::transaction(function () use ($kitabs) {
            foreach ($kitabs as $kitab) {
                for ($page = $kitab['start']; $page <= $kitab['end']; $page++) {
                    Mutun::updateOrCreate(
                        [
                            'kitab' => $kitab['name'],
                            'nomor' => $page,
                        ],
                        [
                            'judul' => "Halaman {$page}",
                            'bab' => null,
                            'urutan' => $page,
                        ]
                    );
                }
            }
        });
    }
}
