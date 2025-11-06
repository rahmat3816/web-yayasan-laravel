<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuranPageSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/data_quran.json');
        if (!File::exists($path)) {
            $this->command->error("❌ File data_quran.json tidak ditemukan di storage/app/");
            return;
        }

        $json = json_decode(File::get($path), true);
        if (!$json || !is_array($json)) {
            $this->command->error("❌ Format data_quran.json tidak valid.");
            return;
        }

        DB::transaction(function () use ($json) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Kosongkan tabel untuk data baru
            DB::table('quran_page_map')->truncate();
            DB::table('quran_surah')->truncate();

            $this->command->info("📖 Mengimpor data halaman Qur'an ...");

            $surahCache = [];

            foreach ($json as $page => $entries) {
                foreach ($entries as $entry) {
                    // Pecah "001. Al-Fatihah"
                    [$idStr, $latinName] = explode('.', $entry['surat'], 2);
                    $surahId = (int) trim($idStr);
                    $namaLatin = trim($latinName);
                    $jumlahAyat = (int) $entry['jumlah_ayat'];

                    // Simpan ke tabel quran_surah jika belum ada
                    if (!isset($surahCache[$surahId])) {
                        DB::table('quran_surah')->insert([
                            'id' => $surahId,
                            'nama' => $namaLatin, // gunakan latin karena nama arab tidak tersedia
                            'nama_latin' => $namaLatin,
                            'jumlah_ayat' => $jumlahAyat,
                        ]);
                        $surahCache[$surahId] = true;
                    }

                    // Simpan ke tabel quran_page_map
                    DB::table('quran_page_map')->insert([
                        'page' => (int) $page,
                        'surah_id' => $surahId,
                        'ayat_awal' => (int) $entry['ayat_mulai'],
                        'ayat_akhir' => (int) $entry['ayat_akhir'],
                    ]);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        $this->command->info("✅ Import selesai! Total halaman Qur'an: " . count($json));
    }
}
