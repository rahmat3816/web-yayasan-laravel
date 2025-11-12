<?php

namespace App\Observers;

use App\Models\GuruJabatan;

class GuruJabatanObserver
{
    public function created(GuruJabatan $assignment): void
    {
        $assignment->loadMissing('jabatan', 'user');
        $roleSlug = $assignment->jabatan?->slug;
        if ($roleSlug && $assignment->user && !$assignment->user->hasRole($roleSlug)) {
            $assignment->user->assignRole($roleSlug);
        }
    }

    public function deleted(GuruJabatan $assignment): void
    {
        $assignment->loadMissing('jabatan', 'user');
        $roleSlug = $assignment->jabatan?->slug;
        $user = $assignment->user;

        if (!$roleSlug || !$user) {
            return;
        }

        $stillHasRole = $user->jabatans()
            ->where('jabatan.slug', $roleSlug)
            ->exists();

        if (!$stillHasRole && $user->hasRole($roleSlug)) {
            $user->removeRole($roleSlug);
        }
    }
}
