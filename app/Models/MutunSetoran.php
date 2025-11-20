<?php

namespace App\Models;

use App\Models\Guru;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutunSetoran extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_id',
        'tanggal',
        'penilai_id',
        'nilai_mutqin',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(MutunTarget::class, 'target_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'penilai_id');
    }
}
