<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranSantri extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_santri';

    protected $fillable = [
        'santri_id',
        'tanggal',
        'kategori_pelanggaran',
        'keterangan',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}

