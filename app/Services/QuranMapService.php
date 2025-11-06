<?php

namespace App\Services;

use App\Models\QuranJuzMap;

class QuranMapService
{
    /**
     * Ambil seluruh peta dalam bentuk array terstruktur (opsional).
     * Format: [ juz => [ [surah_id, surah_name, ayat_mulai, ayat_akhir, jumlah_ayat], ... ] ]
     */
    public function all(): array
    {
        $rows = QuranJuzMap::orderBy('juz')->orderBy('segment_index')->get([
            'juz','surah_id','surah_name','jumlah_ayat','ayat_mulai','ayat_akhir','segment_index'
        ]);

        $out = [];
        foreach ($rows as $r) {
            $out[$r->juz][] = [
                'surah_id'    => (int) $r->surah_id,
                'surah_name'  => $r->surah_name,
                'jumlah_ayat' => $r->jumlah_ayat ? (int)$r->jumlah_ayat : null,
                'ayat_mulai'  => (int) $r->ayat_mulai,
                'ayat_akhir'  => (int) $r->ayat_akhir,
                'segment_index' => (int) $r->segment_index,
            ];
        }

        return $out;
    }

    /**
     * Kembalikan daftar Juz yang mencakup (surah_id, ayah_start..ayah_end).
     * Jika range melewati batas, akan mengembalikan beberapa Juz.
     */
    public function getJuzRangeForAyah(int $surahId, int $ayahStart, int $ayahEnd): array
    {
        $rows = QuranJuzMap::where('surah_id', $surahId)
            ->where(function($q) use ($ayahStart, $ayahEnd) {
                $q->where('ayat_mulai', '<=', $ayahEnd)
                  ->where('ayat_akhir', '>=', $ayahStart);
            })
            ->get(['juz']);

        $juz = $rows->pluck('juz')->unique()->sort()->values()->all();
        return array_map('intval', $juz);
    }

    /**
     * Daftar surah (unik) dalam sebuah Juz.
     */
    public function getSurahListInJuz(int $juz): array
    {
        return QuranJuzMap::where('juz', $juz)
            ->orderBy('surah_id')
            ->get(['surah_id','surah_name'])
            ->unique('surah_id')
            ->mapWithKeys(function($r){
                return [(int)$r->surah_id => $r->surah_name ?: ('Surah '.$r->surah_id)];
            })
            ->all();
    }
}
