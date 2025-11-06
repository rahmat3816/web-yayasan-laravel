<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranJuzMap extends Model
{
    protected $table = 'quran_juz_map';
    protected $fillable = ['juz', 'surah_id', 'ayat_awal', 'ayat_akhir'];
    public $timestamps = false;

    public function surah()
    {
        return $this->belongsTo(QuranSurah::class, 'surah_id');
    }
}
