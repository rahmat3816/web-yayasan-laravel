<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    // Nama tabel di DB
    protected $table = 'units';

    // Kolom yang bisa diisi mass-assignment
    protected $fillable = [
        'nama_unit',     // contoh: "TK Permata Sunnah"
        'kode_unit',     // contoh: "TK", "MI", dst (opsional)
        'keterangan',    // catatan tambahan (opsional)
        // tambahkan kolom lain jika ada di migrasi-mu
    ];

    protected $casts = [
        // tambahkan casting jika ada kolom bertipe khusus
    ];

    /* =========================
     * Relasi
     * ========================= */

    // Unit ↔ Guru (1 : n)
    public function guru()
    {
        return $this->hasMany(Guru::class, 'unit_id');
    }

    // Unit ↔ Santri (1 : n)
    public function santri()
    {
        return $this->hasMany(Santri::class, 'unit_id');
    }

    // Unit ↔ Halaqoh (1 : n)
    public function halaqoh()
    {
        return $this->hasMany(Halaqoh::class, 'unit_id');
    }

    /* =========================
     * Scopes & Helpers
     * ========================= */

    // Cari berdasarkan nama (case-insensitive)
    public function scopeSearch($query, ?string $q)
    {
        return $query->when($q, fn($qq) =>
            $qq->where('nama_unit', 'like', '%'.$q.'%')
               ->orWhere('kode_unit', 'like', '%'.$q.'%')
        );
    }

    // Urutkan default by id ASC
    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    // Label singkat untuk tampilan dropdown
    public function getLabelAttribute(): string
    {
        $kode = $this->kode_unit ? " ({$this->kode_unit})" : '';
        return "{$this->nama_unit}{$kode}";
    }
}
