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
        static::creating(function (User $user) {
            // 🚫 Jangan ubah username/email jika sudah dikirim dari controller
            if (!isset($user->username) || trim($user->username) === '') {
                $first = strtolower(preg_replace('/[^a-z0-9]/i', '', explode(' ', $user->name)[0] ?? 'user'));
                $user->username = $first . str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
            }
            if (!isset($user->email) || trim($user->email) === '') {
                $user->email = $user->username . '@yayasan.local';
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
