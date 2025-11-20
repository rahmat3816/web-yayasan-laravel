<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WaliSantri;
use App\Models\AbsensiMapel;
use App\Models\SakitSantri;
use App\Models\PelanggaranSantri;
use App\Models\Nilai;
use App\Models\Asrama;
use App\Models\SantriHealthLog;

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
        'asrama_id',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'tahun_masuk' => 'integer',
        'asrama_id' => 'integer',
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

    public function asrama()
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }

    public function halaqoh()
    {
        return $this->belongsToMany(\App\Models\Halaqoh::class, 'halaqoh_santri', 'santri_id', 'halaqoh_id')
            ->withTimestamps();
    }

    /**
     * Alias untuk kompatibilitas dengan komponen yang mengharapkan nama relasi jamak.
     */
    public function halaqohs()
    {
        return $this->halaqoh();
    }

    public function user()
    {
        // Tautan via wali_santri.user (bila anak memiliki wali yang login)
        return $this->hasOneThrough(
            User::class,
            WaliSantri::class,
            'santri_id',   // foreign key on wali_santri
            'id',          // foreign key on users
            'id',          // local key on santri
            'user_id'      // local key on wali_santri
        );
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

    public function generateWaliUsername(): string
    {
        $firstName = strtolower(explode(' ', $this->nama)[0] ?? 'wali');
        $random = rand(100, 999);
        return $firstName . $random;
    }

    public function hafalan()
    {
        return $this->hasMany(\App\Models\HafalanQuran::class, 'santri_id');
    }

    public function haditsTargets()
    {
        return $this->hasMany(\App\Models\HaditsTarget::class, 'santri_id');
    }

    public function haditsSetorans()
    {
        return $this->hasManyThrough(
            \App\Models\HaditsSetoran::class,
            \App\Models\HaditsTarget::class,
            'santri_id',
            'target_id'
        );
    }

    public function mutunTargets()
    {
        return $this->hasMany(\App\Models\MutunTarget::class, 'santri_id');
    }

    public function mutunSetorans()
    {
        return $this->hasManyThrough(
            \App\Models\MutunSetoran::class,
            \App\Models\MutunTarget::class,
            'santri_id',
            'target_id'
        );
    }

    public function wali()
    {
        return $this->hasMany(WaliSantri::class, 'santri_id');
    }

    public function absensi_mapel()
    {
        return $this->hasMany(AbsensiMapel::class, 'santri_id');
    }

    public function sakit()
    {
        return $this->hasMany(SakitSantri::class, 'santri_id');
    }

    public function pelanggaran()
    {
        return $this->hasMany(PelanggaranSantri::class, 'santri_id');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'santri_id');
    }

    public function healthLogs()
    {
        return $this->hasMany(SantriHealthLog::class, 'santri_id');
    }
}
