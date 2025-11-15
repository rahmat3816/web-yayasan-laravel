<?php

namespace App\Models;

use App\Models\Guru;
use App\Models\GuruJabatan;
use App\Models\Jabatan;
use App\Models\Santri;
use App\Models\Unit;
use App\Models\WaliSantri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'unit_id',
        'linked_guru_id',
        'linked_santri_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 🧱 Matikan perubahan otomatis pada username/email.
     * Hanya jalankan fallback kalau benar-benar kosong.
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            // ❗ Jangan menimpa kalau sudah di-set dari controller
            if (blank($user->username)) {
                // fallback lama, kalau tidak diisi dari luar
                $user->username = Str::slug(Str::before($user->name, ' '), '');
            }

            if (blank($user->email)) {
                $user->email = Str::lower($user->username) . '@yayasan.local';
            }
        });

        static::saving(function (User $user) {
            // 🚫 Jangan auto-update username/email saat update name
            if ($user->isDirty('name') && !$user->isDirty('username')) {
                // Biarkan username tetap
            }
        });

        // 🔄 Sinkronisasi role string dan Spatie roles
        static::saved(function (User $user) {
            if (!empty($user->role) && class_exists(\Spatie\Permission\Models\Role::class)) {
                try {
                    \Spatie\Permission\Models\Role::findOrCreate($user->role, $user->guard_name ?? 'web');
                    if (!$user->hasRole($user->role)) {
                        $user->syncRoles([$user->role]);
                    }
                } catch (\Throwable $e) {
                    // Diamkan saja, supaya tidak error
                }
            }
        });
    }

    public function isSuperadmin(): bool
    {
        return strtolower($this->role ?? '') === 'superadmin' || $this->hasRole('superadmin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        $panelRoles = [
            'admin_unit',
            'kepala_madrasah',
            'wakamad_kurikulum',
            'wakamad_kesiswaan',
            'wakamad_sarpras',
            'mudir_pondok',
            'naibul_mudir',
            'naibatul_mudir',
        ];

        return $this->hasRole($panelRoles) || $this->hasJabatan($panelRoles);
    }

    public function waliSantri(): HasMany
    {
        return $this->hasMany(WaliSantri::class, 'user_id');
    }

    public function anakAsuh(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'wali_santri', 'user_id', 'santri_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'linked_guru_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function ensureLinkedGuruId(?string $fallbackName = null): ?int
    {
        if ($this->linked_guru_id) {
            return (int) $this->linked_guru_id;
        }

        $candidate = null;

        if ($this->relationLoaded('guru') && $this->guru) {
            $candidate = $this->guru;
        } else {
            if ($this->email && Schema::hasColumn('guru', 'email')) {
                $candidate = Guru::where('email', $this->email)->first();
            }

            if (!$candidate) {
                $candidate = Guru::where('nama', $fallbackName ?? $this->name)->first();
            }
        }

        if ($candidate) {
            $this->linked_guru_id = $candidate->id;
            $this->unit_id = $this->unit_id ?: $candidate->unit_id;
            $this->save();
            return (int) $candidate->id;
        }

        return null;
    }

    public function jabatanAssignments(): HasMany
    {
        return $this->hasMany(GuruJabatan::class, 'user_id');
    }

    public function jabatans(): BelongsToMany
    {
        return $this->belongsToMany(Jabatan::class, 'guru_jabatan', 'user_id', 'jabatan_id')
            ->withPivot('unit_id');
    }

    public function hasJabatan(string|array $slugs, ?int $unitId = null): bool
    {
        $slugs = (array) $slugs;
        return $this->jabatans()
            ->whereIn('jabatan.slug', array_map('strtolower', $slugs))
            ->when($unitId, fn($query) => $query->where('guru_jabatan.unit_id', $unitId))
            ->exists();
    }
}
