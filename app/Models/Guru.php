<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\MusyrifAssignment;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nama',
        'jenis_kelamin', // 'L' atau 'P'
        'unit_id',
        // opsional: 'user_id' jika kamu simpan tautannya di tabel guru
        // 'user_id',
        'email',        // jika ada
        'no_hp',        // jika ada
        'alamat',
        'tanggal_bergabung',
        'nipy',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'tanggal_bergabung' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guru $guru) {
            if (blank($guru->tanggal_bergabung)) {
                $guru->tanggal_bergabung = now()->toDateString();
            }
        });

        static::saving(function (Guru $guru) {
            if (blank($guru->tanggal_bergabung)) {
                $guru->tanggal_bergabung = now()->toDateString();
            }

            if (blank($guru->nipy) && $guru->tanggal_bergabung) {
                $guru->nipy = static::generateNipy($guru->tanggal_bergabung, $guru->id);
            }
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
        return $this->hasOne(\App\Models\Halaqoh::class, 'guru_id');
    }

    public function musyrifAssignments()
    {
        return $this->hasMany(MusyrifAssignment::class, 'guru_id');
    }

    public function activeMusyrifAssignments()
    {
        return $this->musyrifAssignments()->active();
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

    protected static function generateNipy(string $joinDate, ?int $ignoreId = null): string
    {
        $prefix = Carbon::parse($joinDate)->format('Y.m');

        $query = DB::table('guru')
            ->whereNotNull('nipy');

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $maxSequence = (int) ($query
            ->selectRaw("MAX(CAST(SUBSTRING(nipy, -4) AS UNSIGNED)) as max_seq")
            ->value('max_seq') ?? 0);

        $sequence = $maxSequence + 1;
        $candidate = sprintf('%s.%04d', $prefix, $sequence);

        while (DB::table('guru')->where('nipy', $candidate)->exists()) {
            $sequence++;
            $candidate = sprintf('%s.%04d', $prefix, $sequence);
        }

        return $candidate;
    }
}
