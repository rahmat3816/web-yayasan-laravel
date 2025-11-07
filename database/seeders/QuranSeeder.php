<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuranSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // 🔒 Matikan FK agar aman truncate
        // ==============================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if ($this->tableExists('quran_juz_map'))   DB::table('quran_juz_map')->truncate();
        if ($this->tableExists('quran_page_map'))  DB::table('quran_page_map')->truncate();
        if ($this->tableExists('quran_surah'))     DB::table('quran_surah')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ==============================
        // 📘 DAFTAR SURAH (114 total)
        // Kolom: id | nama_surah | jumlah_ayat
        // ==============================
        $surahList = [
            [1, 'Al-Fatihah', 7],
            [2, 'Al-Baqarah', 286],
            [3, 'Ali Imran', 200],
            [4, 'An-Nisa', 176],
            [5, 'Al-Maidah', 120],
            [6, 'Al-An’am', 165],
            [7, 'Al-A’raf', 206],
            [8, 'Al-Anfal', 75],
            [9, 'At-Taubah', 129],
            [10, 'Yunus', 109],
            [11, 'Hud', 123],
            [12, 'Yusuf', 111],
            [13, 'Ar-Ra’d', 43],
            [14, 'Ibrahim', 52],
            [15, 'Al-Hijr', 99],
            [16, 'An-Nahl', 128],
            [17, 'Al-Isra’', 111],
            [18, 'Al-Kahf', 110],
            [19, 'Maryam', 98],
            [20, 'Ta Ha', 135],
            [21, 'Al-Anbiya’', 112],
            [22, 'Al-Hajj', 78],
            [23, 'Al-Mu’minun', 118],
            [24, 'An-Nur', 64],
            [25, 'Al-Furqan', 77],
            [26, 'Asy-Syu’ara’', 227],
            [27, 'An-Naml', 93],
            [28, 'Al-Qasas', 88],
            [29, 'Al-Ankabut', 69],
            [30, 'Ar-Rum', 60],
            [31, 'Luqman', 34],
            [32, 'As-Sajdah', 30],
            [33, 'Al-Ahzab', 73],
            [34, 'Saba’', 54],
            [35, 'Fatir', 45],
            [36, 'Yasin', 83],
            [37, 'As-Saffat', 182],
            [38, 'Sad', 88],
            [39, 'Az-Zumar', 75],
            [40, 'Ghafir', 85],
            [41, 'Fussilat', 54],
            [42, 'Asy-Syura', 53],
            [43, 'Az-Zukhruf', 89],
            [44, 'Ad-Dukhan', 59],
            [45, 'Al-Jasiyah', 37],
            [46, 'Al-Ahqaf', 35],
            [47, 'Muhammad', 38],
            [48, 'Al-Fath', 29],
            [49, 'Al-Hujurat', 18],
            [50, 'Qaf', 45],
            [51, 'Az-Zariyat', 60],
            [52, 'At-Tur', 49],
            [53, 'An-Najm', 62],
            [54, 'Al-Qamar', 55],
            [55, 'Ar-Rahman', 78],
            [56, 'Al-Waqi’ah', 96],
            [57, 'Al-Hadid', 29],
            [58, 'Al-Mujadilah', 22],
            [59, 'Al-Hasyr', 24],
            [60, 'Al-Mumtahanah', 13],
            [61, 'As-Saff', 14],
            [62, 'Al-Jumu’ah', 11],
            [63, 'Al-Munafiqun', 11],
            [64, 'At-Taghabun', 18],
            [65, 'At-Talaq', 12],
            [66, 'At-Tahrim', 12],
            [67, 'Al-Mulk', 30],
            [68, 'Al-Qalam', 52],
            [69, 'Al-Haqqah', 52],
            [70, 'Al-Ma’arij', 44],
            [71, 'Nuh', 28],
            [72, 'Al-Jinn', 28],
            [73, 'Al-Muzzammil', 20],
            [74, 'Al-Muddassir', 56],
            [75, 'Al-Qiyamah', 40],
            [76, 'Al-Insan', 31],
            [77, 'Al-Mursalat', 50],
            [78, 'An-Naba’', 40],
            [79, 'An-Nazi’at', 46],
            [80, 'Abasa', 42],
            [81, 'At-Takwir', 29],
            [82, 'Al-Infitar', 19],
            [83, 'Al-Mutaffifin', 36],
            [84, 'Al-Insyiqaq', 25],
            [85, 'Al-Buruj', 22],
            [86, 'At-Tariq', 17],
            [87, 'Al-A’la', 19],
            [88, 'Al-Ghasyiyah', 26],
            [89, 'Al-Fajr', 30],
            [90, 'Al-Balad', 20],
            [91, 'Asy-Syams', 15],
            [92, 'Al-Lail', 21],
            [93, 'Ad-Duha', 11],
            [94, 'Asy-Syarh', 8],
            [95, 'At-Tin', 8],
            [96, 'Al-‘Alaq', 19],
            [97, 'Al-Qadr', 5],
            [98, 'Al-Bayyinah', 8],
            [99, 'Az-Zalzalah', 8],
            [100, 'Al-‘Adiyat', 11],
            [101, 'Al-Qari’ah', 11],
            [102, 'At-Takatsur', 8],
            [103, 'Al-‘Asr', 3],
            [104, 'Al-Humazah', 9],
            [105, 'Al-Fil', 5],
            [106, 'Quraisy', 4],
            [107, 'Al-Ma’un', 7],
            [108, 'Al-Kautsar', 3],
            [109, 'Al-Kafirun', 6],
            [110, 'An-Nasr', 3],
            [111, 'Al-Lahab', 5],
            [112, 'Al-Ikhlas', 4],
            [113, 'Al-Falaq', 5],
            [114, 'An-Naas', 6],
        ];

        foreach ($surahList as [$id, $nama, $ayat]) {
            DB::table('quran_surah')->insert([
                'id'           => $id,
                'nama_surah'   => $nama, // ✅ gunakan nama_surah
                'jumlah_ayat'  => $ayat,
            ]);
        }

        // =====================================
        // 📄 Pemetaan halaman mushaf (opsional)
        // =====================================
        $pageFile = __DIR__ . '/quran_page_map.json';
        if (file_exists($pageFile)) {
            $pages = json_decode(file_get_contents($pageFile), true);
            if (is_array($pages) && count($pages)) {
                foreach ($pages as $p) {
                    DB::table('quran_page_map')->insert([
                        'page'       => $p['page'],
                        'juz'        => $p['juz'],
                        'surah_id'   => $p['surah_id'],
                        'ayat_awal'  => $p['ayat_awal'],
                        'ayat_akhir' => $p['ayat_akhir'],
                    ]);
                }
                $this->log('✅ quran_page_map dimuat (' . count($pages) . ' baris)');
            }
        } else {
            $this->warn('⚠️ quran_page_map.json tidak ditemukan → dilewati');
        }

        // =====================================
        // 🧭 Pemetaan 30 Juz (opsional)
        // =====================================
        $juzFile = __DIR__ . '/quran_juz_map.json';
        if (file_exists($juzFile)) {
            $juzMap = json_decode(file_get_contents($juzFile), true);
            if (is_array($juzMap)) {
                $count = 0;
                foreach ($juzMap as $juz => $entries) {
                    foreach ($entries as $e) {
                        DB::table('quran_juz_map')->insert([
                            'juz'        => $juz,
                            'surah_id'   => $e['surah'],
                            'ayat_awal'  => $e['ayat_mulai'],
                            'ayat_akhir' => $e['ayat_akhir'],
                        ]);
                        $count++;
                    }
                }
                $this->log("✅ quran_juz_map dimuat ($count baris)");
            }
        } else {
            $this->warn('⚠️ quran_juz_map.json tidak ditemukan → dilewati');
        }

        $this->log('🎉 QuranSeeder selesai tanpa error.');
    }

    // ==============================
    // Helpers
    // ==============================
    private function tableExists(string $table): bool
    {
        try {
            DB::select('select 1 from ' . $table . ' limit 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function log(string $msg): void
    {
        if (property_exists($this, 'command') && $this->command) {
            $this->command->info($msg);
        }
    }

    private function warn(string $msg): void
    {
        if (property_exists($this, 'command') && $this->command) {
            $this->command->warn($msg);
        }
    }
}
