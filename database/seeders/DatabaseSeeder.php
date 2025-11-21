<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder proyek Yayasan As-Sunnah Gorontalo
     */
    public function run(): void
    {
        // ✅ Nonaktifkan foreign key checks sementara (khusus MySQL saja)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Jalankan semua seeder sesuai urutan
        $this->call([
            UnitSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            AsramaSeeder::class,
            MusyrifAssignmentSeeder::class,
            KeluhanSakitSeeder::class,
            PenangananSementaraSeeder::class,
            HaditsSeeder::class,
            MutunSeeder::class,
            KeamananCategorySeeder::class,
            KeamananViolationSeeder::class,
            KeamananKetaatanSeeder::class,
            // Tambahkan seeder lain jika perlu
        ]);

        // ✅ Aktifkan kembali constraint (khusus MySQL)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
