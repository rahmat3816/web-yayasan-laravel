<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mutun extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'kitab',
        'bab',
        'nomor',
        'urutan',
        'teks_arab',
        'teks_terjemah',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(MutunTarget::class);
    }
}
