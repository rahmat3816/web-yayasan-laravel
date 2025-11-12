<?php

namespace App\Observers;

use App\Models\Santri;
use App\Models\User;
use App\Models\WaliSantri;
use Illuminate\Support\Facades\Hash;

class SantriObserver
{
    public function created(Santri $santri): void
    {
        $this->ensureWaliAccount($santri);
    }

    public function updated(Santri $santri): void
    {
        if ($santri->isDirty('nama')) {
            $this->ensureWaliAccount($santri, true);
        }
    }

    protected function ensureWaliAccount(Santri $santri, bool $refreshName = false): void
    {
        $existing = WaliSantri::with('user')
            ->where('santri_id', $santri->id)
            ->first();

        if ($existing && $existing->user) {
            if ($refreshName && $santri->nama && $existing->user->name !== $santri->nama) {
                $existing->user->name = $santri->nama;
                $existing->user->save();
            }

            return;
        }

        $username = $santri->generateWaliUsername();
        while (User::where('username', $username)->exists()) {
            $username = $santri->generateWaliUsername();
        }

        $email = $username . '@wali.siyasgo.id';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $santri->nama ?: 'Wali Santri',
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => 'wali_santri',
            ]
        );

        if (!$user->hasRole('wali_santri')) {
            $user->assignRole('wali_santri');
        }

        WaliSantri::updateOrCreate(
            [
                'santri_id' => $santri->id,
            ],
            [
                'user_id' => $user->id,
                'username_wali' => $username,
            ]
        );
    }
}
