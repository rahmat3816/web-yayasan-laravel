<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SantriHealthLogStatusNotification;

class SantriHealthAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'health_log_id',
        'handled_by',
        'tindakan',
        'rujukan_tempat',
        'catatan',
        'instruksi_at',
    ];

    protected $casts = [
        'instruksi_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (SantriHealthAction $action) {
            $log = $action->log;

            if (! $log) {
                return;
            }

            $statusMap = [
                'observasi' => 'ditangani',
                'obat_ringan' => 'ditangani',
                'rujuk_klinik' => 'dirujuk',
                'rujuk_puskesmas' => 'dirujuk',
                'rujuk_rumahsakit' => 'dirujuk',
                'lainnya' => 'ditangani',
            ];

            $log->status = $statusMap[$action->tindakan] ?? 'ditangani';

            if (str_contains($log->status, 'dirujuk')) {
                $log->perlu_rujukan = true;
            }

            $log->save();

            $notifiables = [];

            if ($log->reporter && $log->reporter->user) {
                $notifiables[] = $log->reporter->user;
            }

            if (! empty($notifiables)) {
                Notification::send(
                    $notifiables,
                    new SantriHealthLogStatusNotification($log, $action)
                );
            }
        });
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(SantriHealthLog::class, 'health_log_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
