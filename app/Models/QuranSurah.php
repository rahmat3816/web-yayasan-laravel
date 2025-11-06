<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranSurah extends Model
{
    protected $table = 'quran_surah';
    protected $fillable = ['id', 'nama', 'nama_latin', 'jumlah_ayat'];
    public $timestamps = false;

    public function ayat()
    {
        return $this->hasMany(QuranAyat::class, 'surah_id');
    }

    public function juzMaps()
    {
        return $this->hasMany(QuranJuzMap::class, 'surah_id');
    }
}
