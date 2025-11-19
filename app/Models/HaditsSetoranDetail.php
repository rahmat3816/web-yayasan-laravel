<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HaditsSetoranDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'setoran_id',
        'segment_id',
        'status',
    ];

    public function setoran(): BelongsTo
    {
        return $this->belongsTo(HaditsSetoran::class, 'setoran_id');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(HaditsSegment::class, 'segment_id');
    }
}

