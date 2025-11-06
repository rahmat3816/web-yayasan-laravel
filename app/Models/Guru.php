<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nama',
        'nip',
        'jenis_kelamin', // 'L' atau 'P'
        'unit_id',
        // opsional: 'user_id' jika kamu simpan tautannya di tabel guru
        // 'user_id',
        'email',        // jika ada
        'no_hp',        // jika ada
    ];

    protected $casts = [
        'unit_id' => 'integer',
    ];

    /* =========================
     * Relasi
     * ========================= */

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function halaqoh()
    {
        return $this->hasOne(Halaqoh::class, 'guru_id');
    }

    public function user()
    {
        // Tautan via users.linked_guru_id
        return $this->hasOne(User::class, 'linked_guru_id', 'id');
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

    public function isPengampu(): bool
    {
        return $this->halaqoh()->exists();
    }
}
