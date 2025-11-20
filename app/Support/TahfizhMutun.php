<?php

namespace App\Support;

use App\Models\User;

class TahfizhMutun
{
    public static function unitIds(): array
    {
        return TahfizhHadits::unitIds();
    }

    public static function userHasManagementAccess(?User $user): bool
    {
        return TahfizhHadits::userHasManagementAccess($user);
    }

    public static function userHasAccess(?User $user): bool
    {
        return TahfizhHadits::userHasAccess($user);
    }

    public static function accessibleSantriIds(?User $user): array
    {
        return TahfizhHadits::accessibleSantriIds($user);
    }

    public static function userHasFullSantriScope(?User $user): bool
    {
        return TahfizhHadits::userHasFullSantriScope($user);
    }

    public static function userCanManageSantri(?User $user, int $santriId): bool
    {
        return TahfizhHadits::userCanManageSantri($user, $santriId);
    }
}
