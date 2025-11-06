<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;

    protected $table = 'santri';

    protected $fillable = [
        'nama',
        'nisn',
        'nisy',
        'jenis_kelamin', // 'L' / 'P'
        'unit_id',
        'tahun_masuk',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'tahun_masuk' => 'integer',
    ];

    /* =========================
     * Auto-generate NISY aman
     * ========================= */
    protected static function booted(): void
    {
        static::creating(function (Santri $santri) {
            // Pastikan tahun_masuk terisi
            $tahun = (int)($santri->tahun_masuk ?: date('Y'));
            $santri->tahun_masuk = $tahun;

            // Prefix berdasar tahun masuk, mis: YSY25
            $prefix = 'YSY' . substr((string)$tahun, -2);

            // Cari NISY terakhir berdasarkan prefix, tidak bergantung created_at
            $last = static::where('nisy', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('nisy');

            $lastNumber = $last ? (int) substr($last, -4) : 0;
            $next = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            $santri->nisy = $prefix . $next;
        });
    }

    /* =========================
     * Relasi
     * ========================= */

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function halaqoh()
    {
        return $this->belongsToMany(Halaqoh::class, 'halaqoh_santri', 'santri_id', 'halaqoh_id')
            ->withTimestamps();
    }

    public function user()
    {
        // Tautan via users.linked_santri_id (jika dipakai)
        return $this->hasOne(User::class, 'linked_santri_id', 'id');
    }

    /* =========================
     * Scopes & Helpers
     * ========================= */

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeLaki($query)
    {
        return $query->where('jenis_kelamin', 'L');
    }

    public function scopePerempuan($query)
    {
        return $query->where('jenis_kelamin', 'P');
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki';
    }
}
