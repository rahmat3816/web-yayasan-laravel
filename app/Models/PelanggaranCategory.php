<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelanggaranCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'sp_threshold',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(PelanggaranType::class, 'kategori_id');
    }
}
