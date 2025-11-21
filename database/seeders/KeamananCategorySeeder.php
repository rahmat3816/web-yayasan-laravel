<?php

namespace Database\Seeders;

use App\Models\PelanggaranCategory;
use Illuminate\Database\Seeder;

class KeamananCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Ibadah', 'deskripsi' => 'Pelanggaran terkait ibadah', 'sp_threshold' => 20],
            ['nama' => 'Pengajaran', 'deskripsi' => 'Pelanggaran terkait pengajaran', 'sp_threshold' => 20],
            ['nama' => 'Asrama', 'deskripsi' => 'Pelanggaran terkait asrama', 'sp_threshold' => 20],
            ['nama' => 'Bahasa', 'deskripsi' => 'Pelanggaran terkait bahasa', 'sp_threshold' => 10],
            ['nama' => 'Konsumsi', 'deskripsi' => 'Pelanggaran terkait konsumsi', 'sp_threshold' => 10],
            ['nama' => 'Perizinan', 'deskripsi' => 'Pelanggaran terkait perizinan', 'sp_threshold' => 20],
            ['nama' => 'Pakaian & Penampilan', 'deskripsi' => 'Pelanggaran terkait pakaian/penampilan', 'sp_threshold' => 20],
            ['nama' => 'Keamanan & Ketertiban', 'deskripsi' => 'Pelanggaran terkait keamanan/ketertiban', 'sp_threshold' => 20],
            ['nama' => 'Hiburan', 'deskripsi' => 'Pelanggaran terkait hiburan/media', 'sp_threshold' => 20],
        ];

        foreach ($data as $row) {
            PelanggaranCategory::updateOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
