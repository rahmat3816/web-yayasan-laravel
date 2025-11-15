<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaliSantri extends Model
{
    protected $table = 'wali_santri';
    protected $fillable = ['santri_id', 'user_id', 'username_wali'];
    protected $casts = [
        'santri_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
