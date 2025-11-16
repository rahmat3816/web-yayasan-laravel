<?php

namespace Database\Seeders;

use App\Models\PenangananSementara;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PenangananSementaraSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Observasi / istirahat',
            'Obat ringan (analgesik / antipiretik)',
            'Kompres',
            'Konsumsi air / hidrasi',
            'Perban / perawatan luka ringan',
            'Rujuk klinik',
            'Rujuk puskesmas',
            'Rujuk rumah sakit',
            'Isolasi sementara',
            'Lainnya',
        ];

        foreach ($items as $idx => $nama) {
            PenangananSementara::updateOrCreate(
                ['slug' => Str::slug($nama)],
                ['nama' => $nama, 'urutan' => $idx + 1]
            );
        }
    }
}
