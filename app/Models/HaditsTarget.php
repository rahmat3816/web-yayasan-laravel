<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HaditsTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'hadits_id',
        'tahun',
        'semester',
        'status',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function hadits(): BelongsTo
    {
        return $this->belongsTo(Hadits::class);
    }

    public function setorans(): HasMany
    {
        return $this->hasMany(HaditsSetoran::class, 'target_id');
    }
}

