<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

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
}
