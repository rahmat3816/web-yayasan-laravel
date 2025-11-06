<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Unit;

class UnitPolicy
{
    /**
     * Hanya SUPERADMIN yang boleh melakukan aksi apa pun terkait Unit.
     */
    protected function isSuperadmin(User $user): bool
    {
        // Kolom 'role' milik aplikasi + dukungan Spatie
        return strtolower($user->role ?? '') === 'superadmin'
            || (method_exists($user, 'hasRole') && $user->hasRole('superadmin'));
    }

    public function viewAny(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->isSuperadmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperadmin($user);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->isSuperadmin($user);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->isSuperadmin($user);
    }

    public function restore(User $user, Unit $unit): bool
    {
        return $this->isSuperadmin($user);
    }

    public function forceDelete(User $user, Unit $unit): bool
    {
        return $this->isSuperadmin($user);
    }
}
