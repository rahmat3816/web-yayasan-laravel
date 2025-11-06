<?php

namespace App\Observers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GuruObserver
{
    /**
     * Saat Guru baru dibuat: buat/tautkan akun User.
     */
    public function created(Guru $guru): void
    {
        // Jika sudah ada user tertaut, abaikan
        if ($guru->user_id || $guru->email && User::where('email', $guru->email)->exists()) {
            $this->linkUserIfExists($guru);
            return;
        }

        // Generate username unik dari nama guru
        $base = Str::slug($guru->nama, '');
        if ($base === '') $base = 'guru';
        $username = $this->uniqueUsername($base);

        // Pakai email jika tersedia, kalau tidak buat placeholder
        $email = $guru->email ?: $username.'@yayasan.local';

        $user = User::create([
            'name'            => $guru->nama,
            'email'           => $email,
            'username'        => $username,
            'password'        => Hash::make('password'), // TODO: force change on first login
            'role'            => 'guru',
            'unit_id'         => $guru->unit_id,
            'linked_guru_id'  => $guru->id,
            'linked_santri_id'=> null,
        ]);

        // Opsional: simpan user_id di tabel guru kalau ada kolomnya
        if ($guru->isFillable('user_id')) {
            $guru->user_id = $user->id;
            $guru->saveQuietly();
        }
    }

    /**
     * Saat Guru diperbarui: sinkron nama/email ke User tertaut (jika ada).
     */
    public function updated(Guru $guru): void
    {
        $user = $this->findLinkedUser($guru);
        if (!$user) return;

        $dirty = [];

        if ($guru->isDirty('nama')) {
            $dirty['name'] = $guru->nama;
        }
        if ($guru->isDirty('email') && $guru->email) {
            // Hindari konflik email duplikat
            if (!User::where('email', $guru->email)->where('id', '!=', $user->id)->exists()) {
                $dirty['email'] = $guru->email;
            }
        }
        if ($guru->isDirty('unit_id')) {
            $dirty['unit_id'] = $guru->unit_id;
        }

        if (!empty($dirty)) {
            $user->fill($dirty)->save();
        }
    }

    /**
     * Util: temukan user tertaut via linked_guru_id atau email.
     */
    protected function findLinkedUser(Guru $guru): ?User
    {
        if ($guru->user_id) {
            return User::find($guru->user_id);
        }
        $user = User::where('linked_guru_id', $guru->id)->first();
        if ($user) return $user;

        if ($guru->email) {
            return User::where('email', $guru->email)->first();
        }
        return null;
    }

    /**
     * Bila user sudah ada (via email), tautkan linked_guru_id/unit bila perlu.
     */
    protected function linkUserIfExists(Guru $guru): void
    {
        $user = $this->findLinkedUser($guru);
        if (!$user && $guru->email) {
            $user = User::where('email', $guru->email)->first();
        }
        if ($user) {
            $changed = false;
            if (!$user->linked_guru_id) { $user->linked_guru_id = $guru->id; $changed = true; }
            if ($user->unit_id !== $guru->unit_id) { $user->unit_id = $guru->unit_id; $changed = true; }
            if ($user->role !== 'guru') { $user->role = 'guru'; $changed = true; }
            if ($changed) $user->save();
            if ($guru->isFillable('user_id') && !$guru->user_id) {
                $guru->user_id = $user->id;
                $guru->saveQuietly();
            }
        }
    }

    /**
     * Buat username unik dengan menambahkan angka jika perlu.
     */
    protected function uniqueUsername(string $base): string
    {
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.$i;
            $i++;
            if ($i > 9999) { // fallback
                $username = $base.Str::random(4);
                break;
            }
        }
        return $username;
    }
}
