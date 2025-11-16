<?php

namespace App\Policies;

use App\Models\SantriHealthLog;
use App\Models\User;

class SantriHealthLogPolicy
{
    protected function hasFullAccess(User $user): bool
    {
        return $user->hasKesehatanFullAccess();
    }

    public function viewAny(User $user): bool
    {
        return $this->hasFullAccess($user) || $user->isActiveMusyrif();
    }

    protected function matchesGender(User $user, SantriHealthLog $log): bool
    {
        $gender = $log->santri?->jenis_kelamin;

        if (! $gender) {
            return true;
        }

        $scope = $user->kesehatanGenderScope();

        if (! $scope) {
            return true;
        }

        return $scope === $gender;
    }

    public function view(User $user, SantriHealthLog $log): bool
    {
        if ($this->hasFullAccess($user)) {
            return $this->matchesGender($user, $log);
        }

        return $user->isActiveMusyrif() && $log->isReportedByUser($user);
    }

    public function create(User $user): bool
    {
        return $this->hasFullAccess($user) || $user->isActiveMusyrif();
    }

    public function update(User $user, SantriHealthLog $log): bool
    {
        if ($this->hasFullAccess($user)) {
            return $this->matchesGender($user, $log);
        }

        return $user->isActiveMusyrif() && $log->isReportedByUser($user);
    }

    public function delete(User $user, SantriHealthLog $log): bool
    {
        return $this->hasFullAccess($user) && $this->matchesGender($user, $log);
    }
}
