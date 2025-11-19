<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HaditsSetoran extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_id',
        'tanggal',
        'penilai_id',
        'nilai_tajwid',
        'nilai_mutqin',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(HaditsTarget::class, 'target_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'penilai_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(HaditsSetoranDetail::class, 'setoran_id');
    }
}

