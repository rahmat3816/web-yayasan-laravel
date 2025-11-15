<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HafalanTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'created_by',
        'tahun',
        'juz',
        'surah_start_id',
        'surah_end_id',
        'ayat_start',
        'ayat_end',
        'total_ayat',
        'target_per_bulan',
        'target_per_minggu',
        'target_per_hari',
    ];

    protected $casts = [
        'santri_id' => 'integer',
        'created_by' => 'integer',
        'tahun' => 'integer',
        'juz' => 'integer',
        'surah_start_id' => 'integer',
        'surah_end_id' => 'integer',
        'ayat_start' => 'integer',
        'ayat_end' => 'integer',
        'total_ayat' => 'integer',
        'target_per_bulan' => 'integer',
        'target_per_minggu' => 'integer',
        'target_per_hari' => 'integer',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function surahStart()
    {
        return $this->belongsTo(QuranSurah::class, 'surah_start_id');
    }

    public function surahEnd()
    {
        return $this->belongsTo(QuranSurah::class, 'surah_end_id');
    }
}

