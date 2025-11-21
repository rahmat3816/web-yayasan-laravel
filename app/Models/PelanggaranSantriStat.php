<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranSantriStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'poin_pelanggaran',
        'poin_penghargaan',
        'total_poin',
        'total_poin',
        'sp_level',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }
}
