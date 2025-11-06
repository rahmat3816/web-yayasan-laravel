<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Halaqoh extends Model
{
    use HasFactory;

    protected $table = 'halaqoh';

    protected $fillable = [
        'nama_halaqoh',
        'guru_id',
        'unit_id',
        'keterangan',
    ];

    protected $casts = [
        'guru_id' => 'integer',
        'unit_id' => 'integer',
    ];

    /**
     * 🧑‍🏫 Relasi ke Guru Pengampu
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * 🏫 Relasi ke Unit Pendidikan
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * 👨‍🎓 Relasi ke Santri (Many-to-Many) via pivot 'halaqoh_santri'
     */
    public function santri()
    {
        return $this->belongsToMany(Santri::class, 'halaqoh_santri', 'halaqoh_id', 'santri_id')
            ->withTimestamps();
    }

    /* =========================
     * Scopes & Helpers
     * ========================= */

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeWithBasic($query)
    {
        return $query->with(['guru:id,nama,unit_id', 'santri:id,nama']);
    }

    public static function guruSudahPunyaHalaqoh(int $guruId, ?int $excludeId = null): bool
    {
        return static::when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where('guru_id', $guruId)
            ->exists();
    }

    /**
     * Safety net: 1 guru hanya boleh punya 1 halaqoh.
     * (Controller sudah cek; ini lapisan tambahan.)
     */
    protected static function booted(): void
    {
        static::creating(function (Halaqoh $h) {
            if (self::guruSudahPunyaHalaqoh((int)$h->guru_id)) {
                throw new \RuntimeException('Guru ini sudah memiliki halaqoh.');
            }
        });

        static::updating(function (Halaqoh $h) {
            if (self::guruSudahPunyaHalaqoh((int)$h->guru_id, (int)$h->id)) {
                throw new \RuntimeException('Guru ini sudah memiliki halaqoh lain.');
            }
        });
    }
}
