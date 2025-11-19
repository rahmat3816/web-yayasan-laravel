<?php

namespace App\Support;

use App\Models\Guru;
use App\Models\Halaqoh;
use App\Models\Santri;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TahfizhHadits
{
    protected const ALLOWED_UNIT_NAMES = [
        'Pondok Pesantren As-Sunnah Gorontalo',
        'MTS As-Sunnah Gorontalo',
        'MA As-Sunnah Limboto Barat',
    ];

    protected const ALLOWED_ROLES = [
        'superadmin',
        'koor_tahfizh_putra',
        'koor_tahfizh_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koord_tahfizh_akhwat',
        'guru',
    ];

    protected const MANAGER_ROLES = [
        'superadmin',
        'koor_tahfizh_putra',
        'koor_tahfizh_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
    ];

    protected static ?array $unitIdsCache = null;

    public static function unitNames(): array
    {
        return self::ALLOWED_UNIT_NAMES;
    }

    public static function unitIds(): array
    {
        if (self::$unitIdsCache === null) {
            self::$unitIdsCache = Unit::whereIn('nama_unit', self::unitNames())->pluck('id')->all();
        }

        return self::$unitIdsCache;
    }

    public static function roles(): array
    {
        return self::ALLOWED_ROLES;
    }

    public static function managerRoles(): array
    {
        return self::MANAGER_ROLES;
    }

    public static function userHasAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return true;
        }

        if (! $user->hasAnyRole(self::roles())) {
            return false;
        }

        $unitIds = self::unitIds();

        if ($user->unit_id && in_array((int) $user->unit_id, $unitIds, true)) {
            return true;
        }

        $guru = $user->guru;

        if (! $guru && $user->linked_guru_id) {
            $guru = Guru::find($user->linked_guru_id);
        }

        if (! $guru) {
            $guruId = $user->ensureLinkedGuruId($user->name ?? null);
            if ($guruId) {
                $guru = Guru::find($guruId);
            }
        }

        if ($guru && in_array((int) $guru->unit_id, $unitIds, true)) {
            return true;
        }

        $jabatanUnitIds = $user->jabatans()
            ->pluck('guru_jabatan.unit_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty(array_intersect($jabatanUnitIds, $unitIds))) {
            return true;
        }

        return false;
    }
    public static function userHasManagementAccess(?User $user): bool
    {
        return $user?->hasAnyRole(self::managerRoles()) ?? false;
    }

    public static function userHasFullSantriScope(?User $user): bool
    {
        return $user?->hasRole('superadmin') ?? false;
    }

    public static function accessibleSantriIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if (self::userHasFullSantriScope($user)) {
            return Santri::whereIn('unit_id', self::unitIds())->pluck('id')->all();
        }

        $guruId = $user->linked_guru_id ?? $user->ensureLinkedGuruId($user->name ?? null);

        if (! $guruId) {
            return [];
        }

        $halaqohIds = Halaqoh::where('guru_id', $guruId)->pluck('id');

        if ($halaqohIds->isEmpty()) {
            return [];
        }

        return DB::table('halaqoh_santri')
            ->whereIn('halaqoh_id', $halaqohIds)
            ->pluck('santri_id')
            ->unique()
            ->values()
            ->all();
    }

    public static function userCanManageSantri(?User $user, int $santriId): bool
    {
        return in_array($santriId, self::accessibleSantriIds($user), true);
    }
}
