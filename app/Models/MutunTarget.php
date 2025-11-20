<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MutunTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'mutun_id',
        'tahun',
        'semester',
        'status',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function mutun(): BelongsTo
    {
        return $this->belongsTo(Mutun::class);
    }

    public function setorans(): HasMany
    {
        return $this->hasMany(MutunSetoran::class, 'target_id');
    }
}
