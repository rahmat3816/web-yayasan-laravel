<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Santri;
use App\Models\Guru;
use App\Models\Halaqoh;

class HafalanQuran extends Model
{
    use HasFactory;

    protected $table = 'hafalan_quran';

    protected $fillable = [
        'unit_id','halaqoh_id','guru_id','santri_id','tanggal_setor',
        'mode','page_start','page_end','surah_id','ayah_start','ayah_end',
        'juz_start','juz_end','status','catatan',
        'penilaian_tajwid','penilaian_mutqin','penilaian_adab'
    ];

    protected $casts = [
        'tanggal_setor' => 'date',
    ];

    public function santri()  { return $this->belongsTo(Santri::class); }
    public function guru()    { return $this->belongsTo(Guru::class); }
    public function halaqoh() { return $this->belongsTo(Halaqoh::class); }
}
