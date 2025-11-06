<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ImportQuranJuzMap extends Command
{
    protected $signature = 'quran:import-juz
                            {--path= : Path file JSON (default: storage/app/quran/data_quran.json)}
                            {--fresh : Hapus data lama sebelum import}';

    protected $description = 'Import peta Juz → (Surah, ayat_mulai, ayat_akhir) dari JSON ke tabel quran_juz_map';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/quran/data_quran.json');

        if (!is_file($path)) {
            $this->error("File tidak ditemukan: $path");
            return self::FAILURE;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $this->error('Format JSON tidak valid.');
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Menghapus data lama tabel quran_juz_map...');
            DB::table('quran_juz_map')->truncate();
        }

        $this->info('Mengimpor data...');

        $rows = [];
        foreach ($data as $juzKey => $segments) {
            $juz = (int) $juzKey;
            $index = 1;

            foreach ($segments as $seg) {
                $surahText   = Arr::get($seg, 'surat', '');
                $surahId     = $this->extractSurahNumber($surahText) ?? 0;
                $surahName   = $this->extractSurahName($surahText) ?? null;
                $jumlahAyat  = (int) Arr::get($seg, 'jumlah_ayat', 0);
                $ayatMulai   = (int) Arr::get($seg, 'ayat_mulai', 0);
                $ayatAkhir   = (int) Arr::get($seg, 'ayat_akhir', 0);

                if ($juz < 1 || $juz > 30 || $surahId < 1 || $surahId > 114 || $ayatMulai < 1 || $ayatAkhir < $ayatMulai) {
                    $this->warn("Lewati entri tidak valid: juz=$juz, surah=$surahId, ayat=$ayatMulai-$ayatAkhir");
                    continue;
                }

                $rows[] = [
                    'juz'           => $juz,
                    'surah_id'      => $surahId,
                    'surah_name'    => $surahName,
                    'jumlah_ayat'   => $jumlahAyat ?: null,
                    'ayat_mulai'    => $ayatMulai,
                    'ayat_akhir'    => $ayatAkhir,
                    'segment_index' => $index++,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Upsert via Query Builder (tanpa model)
        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            DB::table('quran_juz_map')->upsert(
                $chunk,
                ['juz','surah_id','ayat_mulai','ayat_akhir'],
                ['surah_name','jumlah_ayat','segment_index','updated_at']
            );
        }

        $this->info('Selesai mengimpor '.count($rows).' baris.');
        return self::SUCCESS;
    }

    protected function extractSurahNumber(string $s): ?int
    {
        if (preg_match('/^\s*(\d{3})\./', $s, $m)) {
            return (int) ltrim($m[1], '0');
        }
        return null;
    }

    protected function extractSurahName(string $s): ?string
    {
        if (preg_match('/^\s*\d{3}\.\s*(.+)$/u', $s, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
