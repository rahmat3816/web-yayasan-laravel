<?php

namespace Database\Seeders;

use App\Models\KetaatanType;
use Illuminate\Database\Seeder;

class KeamananKetaatanSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama' => 'Puasa sunnah Senin/Kamis 1 bulan', 'poin_pengurang' => 20, 'catatan' => null],
            ['nama' => 'Mengikuti ta’lim 1 bulan', 'poin_pengurang' => 20, 'catatan' => null],
            ['nama' => 'Menambah hafalan hadits 30 hadits/bulan', 'poin_pengurang' => 20, 'catatan' => null],
            ['nama' => 'Shalat sunnah sebulan penuh', 'poin_pengurang' => 25, 'catatan' => null],
            ['nama' => 'Tidak terlambat shalat berjamaah 1 bulan', 'poin_pengurang' => 30, 'catatan' => null],
            ['nama' => 'Tidak terlambat masuk sekolah 1 bulan', 'poin_pengurang' => 30, 'catatan' => null],
            ['nama' => 'Menambah hafalan Qur’an 1 juz/bulan', 'poin_pengurang' => 30, 'catatan' => null],
            ['nama' => 'Tidak melakukan pelanggaran 1 bulan', 'poin_pengurang' => 30, 'catatan' => null],
            ['nama' => 'Aktif OSPAS', 'poin_pengurang' => 30, 'catatan' => null],
        ];

        foreach ($items as $item) {
            KetaatanType::updateOrCreate(
                ['nama' => $item['nama']],
                [
                    'poin_pengurang' => $item['poin_pengurang'],
                    'catatan' => $item['catatan'],
                    'aktif' => true,
                ]
            );
        }
    }
}
