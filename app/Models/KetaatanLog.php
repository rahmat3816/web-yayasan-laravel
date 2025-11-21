<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetaatanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'ketaatan_type_id',
        'poin',
        'catatan',
        'dibuat_oleh',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(KetaatanType::class, 'ketaatan_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
