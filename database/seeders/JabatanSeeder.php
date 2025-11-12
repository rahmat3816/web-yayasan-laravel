<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = config('jabatan.roles', []);

        foreach ($definitions as $slug => $meta) {
            $label = $meta['label'] ?? Str::headline($slug);

            Jabatan::updateOrCreate(
                ['slug' => $slug],
                ['nama_jabatan' => $label]
            );
        }
    }
}
