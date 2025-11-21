<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PelanggaranType extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_id',
        'nama',
        'deskripsi',
        'poin_default',
        'langsung_sp3',
        'aktif',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PelanggaranCategory::class, 'kategori_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PelanggaranLog::class, 'pelanggaran_type_id');
    }
}
