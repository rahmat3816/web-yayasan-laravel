<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuranSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/data_quran.json');

        if (!File::exists($path)) {
            $this->command->error("❌ File data_quran.json tidak ditemukan di storage/app/");
            return;
        }

        $this->command->info("📖 Memuat data dari data_quran.json ...");

        $json = json_decode(File::get($path), true);

        if (!$json || !isset($json['surah']) || !isset($json['juz_map'])) {
            $this->command->error("❌ Format data_quran.json tidak valid.");
            return;
        }

        DB::transaction(function () use ($json) {
            // Matikan foreign key sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Kosongkan tabel
            DB::table('quran_juz_map')->truncate();
            DB::table('quran_ayat')->truncate();
            DB::table('quran_surah')->truncate();

            // =============================
            // 🕋 1. Insert Surah
            // =============================
            foreach ($json['surah'] as $surah) {
                DB::table('quran_surah')->insert([
                    'id'           => $surah['id'],
                    'nama'         => $surah['nama_arab'] ?? $surah['nama'],
                    'nama_latin'   => $surah['nama_latin'] ?? $surah['latin'] ?? 'Surah ' . $surah['id'],
                    'jumlah_ayat'  => $surah['jumlah_ayat'] ?? 0,
                ]);
            }

            // =============================
            // 🧭 2. Insert Juz Map
            // =============================
            foreach ($json['juz_map'] as $juz) {
                $juzNumber = $juz['juz'] ?? null;
                if (!$juzNumber || empty($juz['maps'])) continue;

                foreach ($juz['maps'] as $map) {
                    DB::table('quran_juz_map')->insert([
                        'juz'        => $juzNumber,
                        'surah_id'   => $map['surah_id'],
                        'ayat_awal'  => $map['ayat_awal'],
                        'ayat_akhir' => $map['ayat_akhir'],
                    ]);
                }
            }

            // =============================
            // 📜 3. Insert Ayat (optional)
            // =============================
            if (isset($json['ayat']) && is_array($json['ayat'])) {
                foreach ($json['ayat'] as $ayat) {
                    DB::table('quran_ayat')->insert([
                        'surah_id'   => $ayat['surah_id'],
                        'nomor_ayat' => $ayat['nomor_ayat'],
                        'teks_arab'  => $ayat['teks_arab'] ?? '',
                        'teks_latin' => $ayat['teks_latin'] ?? '',
                        'terjemahan' => $ayat['terjemahan'] ?? '',
                    ]);
                }
            }

            // Aktifkan lagi FK
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        $this->command->info("✅ Import data Qur'an berhasil disimpan ke database!");
    }
}
