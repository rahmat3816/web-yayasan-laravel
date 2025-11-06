<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranAyat extends Model
{
    protected $table = 'quran_ayat';
    protected $fillable = ['surah_id', 'nomor_ayat', 'teks_arab', 'teks_latin', 'terjemahan'];
    public $timestamps = false;

    public function surah()
    {
        return $this->belongsTo(QuranSurah::class, 'surah_id');
    }
}
