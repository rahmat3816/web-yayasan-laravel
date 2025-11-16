<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class MusyrifAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'asrama_id',
        'mulai_tugas',
        'selesai_tugas',
        'status',
        'shift',
        'catatan',
    ];

    protected $casts = [
        'mulai_tugas' => 'date',
        'selesai_tugas' => 'date',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function asrama(): BelongsTo
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }

    public function healthLogs(): HasMany
    {
        return $this->hasMany(SantriHealthLog::class, 'musyrif_assignment_id');
    }

    public function scopeActive($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where('status', 'aktif')
            ->where(function ($q) use ($today) {
                $q->whereNull('mulai_tugas')->orWhere('mulai_tugas', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('selesai_tugas')->orWhere('selesai_tugas', '>=', $today);
            });
    }
}
