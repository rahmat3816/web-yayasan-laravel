<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SakitSantri extends Model
{
    use HasFactory;

    protected $table = 'sakit_santri';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'keterangan',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}

