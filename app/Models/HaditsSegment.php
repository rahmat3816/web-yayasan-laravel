<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HaditsSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hadits_id',
        'urutan',
        'teks',
    ];

    public function hadits(): BelongsTo
    {
        return $this->belongsTo(Hadits::class);
    }

    public function setoranDetails(): HasMany
    {
        return $this->hasMany(HaditsSetoranDetail::class, 'segment_id');
    }
}

