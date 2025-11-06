<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ✅ Wajib untuk Spatie Permission
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * Spatie guard name (default 'web').
     * Pastikan sesuai dengan guard yang kamu pakai di config/auth.php
     */
    protected string $guard_name = 'web';

    /**
     * Kolom yang boleh di-mass assign.
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',              // kolom role milik app (boleh tetap dipakai bersamaan dengan Spatie roles)
        'unit_id',
        'linked_guru_id',
        'linked_santri_id',
        // tambahkan kolom lain jika ada (mis. 'remember_token' tidak perlu di-fillable)
    ];

    /**
     * Sembunyikan saat serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting kolom.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Sinkronkan kolom 'role' (string) dengan Spatie Role saat disimpan.
     * Opsional, tapi membantu konsistensi jika kamu tetap menyimpan role string di kolom 'role'.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user) {
            // Jika package Spatie tersedia dan kolom 'role' berisi sesuatu
            if (!empty($user->role) && class_exists(\Spatie\Permission\Models\Role::class)) {
                try {
                    // Buat role jika belum ada (opsional; kalau tidak ingin auto-create, bisa dicegah)
                    \Spatie\Permission\Models\Role::findOrCreate($user->role, $user->guard_name ?? 'web');

                    // Sync satu role utama sesuai kolom 'role'
                    if (!$user->hasRole($user->role)) {
                        $user->syncRoles([$user->role]);
                    }
                } catch (\Throwable $e) {
                    // Diamkan agar tidak memblok proses save jika role belum disetup
                    // Kamu bisa log error jika perlu: \Log::warning($e->getMessage());
                }
            }
        });
    }

    /**
     * Helper optional (tidak wajib).
     */
    public function isSuperadmin(): bool
    {
        return strtolower($this->role ?? '') === 'superadmin' || $this->hasRole('superadmin');
    }
}
