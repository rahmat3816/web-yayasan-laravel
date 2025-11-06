<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Santri;

class UpdateNisyForExistingSantriSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = date('Y');
        $counter = 1;

        $santriTanpaNisy = Santri::whereNull('nisy')->orWhere('nisy', '')->get();

        foreach ($santriTanpaNisy as $santri) {
            $newNumber = str_pad($counter, 4, '0', STR_PAD_LEFT);
            $santri->nisy = 'YSY' . date('y', strtotime($tahun)) . $newNumber;
            $santri->tahun_masuk = $santri->tahun_masuk ?? $tahun;
            $santri->save();
            $counter++;
        }

        echo "✅ Update NISY selesai. {$counter} santri diperbarui.\n";
    }
}
