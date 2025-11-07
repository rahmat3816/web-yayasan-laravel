<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuranAyatSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("📘 Menjalankan QuranAyatSeeder...");

        // Lokasi yang dicari
        $possiblePaths = [
            storage_path('app/quran/data_quran.json'),
            storage_path('app/data/data_quran.json'),
            base_path('data_quran.json'),
            resource_path('data/data_quran.json'),
        ];

        $jsonPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $jsonPath = $path;
                break;
            }
        }

        // Jika belum ada, coba pindahkan dari root proyek
        if (!$jsonPath) {
            $rootPath = base_path('data_quran.json');
            $targetDir = storage_path('app/quran');

            if (file_exists($rootPath)) {
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                copy($rootPath, $targetDir . '/data_quran.json');
                $jsonPath = $targetDir . '/data_quran.json';
                $this->command->warn("📦 File data_quran.json dipindahkan otomatis ke $jsonPath");
            }
        }

        // Validasi akhir
        if (!$jsonPath || !file_exists($jsonPath)) {
            $this->command->error("❌ File data_quran.json tidak ditemukan. Letakkan di storage/app/quran/");
            return;
        }

        // Baca file JSON
        $json = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($json) || empty($json)) {
            $this->command->error("❌ Gagal membaca atau format data_quran.json tidak valid.");
            return;
        }

        // Bersihkan tabel sebelum isi ulang
        DB::table('quran_ayat')->truncate();

        $insertCount = 0;
        foreach ($json as $juz => $entries) {
            foreach ($entries as $entry) {
                if (!isset($entry['surat'])) continue;
                if (!preg_match('/^(\d{3})/', $entry['surat'], $m)) continue;
                $surahId = (int) ltrim($m[1], '0');
                $ayatMulai = (int) ($entry['ayat_mulai'] ?? 1);
                $ayatAkhir = (int) ($entry['ayat_akhir'] ?? $ayatMulai);

                for ($i = $ayatMulai; $i <= $ayatAkhir; $i++) {
                    $insertCount++;
                    DB::table('quran_ayat')->insert([
                        'surah_id'    => $surahId,
                        'juz'         => (int)$juz,
                        'ayat_ke'     => $i,
                        'ayat_global' => $insertCount,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

        $this->command->info("✅ QuranAyatSeeder selesai. Total ayat dimasukkan: {$insertCount}");
    }
}
