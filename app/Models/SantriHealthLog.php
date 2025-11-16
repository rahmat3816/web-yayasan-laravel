<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class SantriHealthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'asrama_id',
        'reporter_id',
        'musyrif_assignment_id',
        'tanggal_sakit',
        'keluhan',
        'keluhan_id',
        'keluhan_lain',
        'penanganan_id',
        'tingkat',
        'penanganan_sementara',
        'status',
        'perlu_rujukan',
    ];

    protected $casts = [
        'tanggal_sakit' => 'date',
        'perlu_rujukan' => 'boolean',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function asrama(): BelongsTo
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'reporter_id');
    }

    public function keluhanRef(): BelongsTo
    {
        return $this->belongsTo(\App\Models\KeluhanSakit::class, 'keluhan_id');
    }

    public function penangananRef(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PenangananSementara::class, 'penanganan_id');
    }

    public function musyrifAssignment(): BelongsTo
    {
        return $this->belongsTo(MusyrifAssignment::class, 'musyrif_assignment_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(SantriHealthAction::class, 'health_log_id');
    }

    public function isReportedByUser(User $user): bool
    {
        if (! $user->linked_guru_id) {
            return false;
        }

        return (int) $this->reporter_id === (int) $user->linked_guru_id;
    }
}
