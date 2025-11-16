<?php

namespace Database\Seeders;

use App\Models\KeluhanSakit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KeluhanSakitSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Sakit kepala',
            'Sakit telinga',
            'Sakit mata',
            'Sakit hidung',
            'Sakit tenggorokan',
            'Sakit gigi',
            'Sakit dada',
            'Sakit perut',
            'Sakit kaki',
            'Demam',
            'Batuk',
            'Pilek',
            'Mual / muntah',
            'Diare',
            'Pusing',
            'Cedera / luka',
            'Lainnya',
        ];

        foreach ($items as $index => $nama) {
            KeluhanSakit::updateOrCreate(
                ['slug' => Str::slug($nama)],
                ['nama' => $nama, 'urutan' => $index + 1]
            );
        }
    }
}
