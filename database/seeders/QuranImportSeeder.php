<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuranImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data_quran.json');

        if (!File::exists($path)) {
            $this->command->error("❌ File data_quran.json tidak ditemukan di folder seeders.");
            return;
        }

        $json = json_decode(File::get($path), true);
        if (!$json) {
            $this->command->error("❌ Gagal membaca data_quran.json (format tidak valid).");
            return;
        }

        // 🚫 Matikan sementara foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('quran_juz_map')->truncate();
        DB::table('quran_surah')->truncate();

        // ✅ Nyalakan kembali setelah selesai
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("🚀 Mengimpor data Qur’an...");

        foreach ($json['surahs'] ?? [] as $surah) {
            DB::table('quran_surah')->insert([
                'id' => $surah['number'],
                'nama' => $surah['name'],
                'nama_latin' => $surah['latin'],
                'jumlah_ayat' => $surah['total_verses'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($json['juzs'] ?? [] as $juzEntry) {
            foreach ($juzEntry['ranges'] ?? [] as $range) {
                DB::table('quran_juz_map')->insert([
                    'juz' => $juzEntry['juz'],
                    'surah_id' => $range['surah'],
                    'ayat_awal' => $range['from'],
                    'ayat_akhir' => $range['to'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("✅ Import selesai: Surah & Juz Map berhasil dimasukkan.");
    }
}
