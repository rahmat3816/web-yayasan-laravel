<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPendidikan extends Model
{
    protected $table = 'kalender_pendidikan';
    protected $fillable = ['tahun_ajaran', 'tanggal_mulai', 'libur', 'event', 'unit_id'];
    protected $casts = [
        'libur' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
