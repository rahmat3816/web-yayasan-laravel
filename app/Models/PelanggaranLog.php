<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'pelanggaran_type_id',
        'kategori_id',
        'poin',
        'catatan',
        'dibuat_oleh',
        'sp_level',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PelanggaranType::class, 'pelanggaran_type_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PelanggaranCategory::class, 'kategori_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
