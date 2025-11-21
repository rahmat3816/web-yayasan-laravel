<?php

namespace App\Support;

use App\Models\Santri;
use App\Models\User;
use App\Models\MusyrifAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KeamananAccess
{
    protected const MANAGER_ROLES = [
        'superadmin',
        'koor_keamanan',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
    ];

    protected const ROLES = [
        'superadmin',
        'koor_keamanan',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'musyrif',
        'musyrifah',
    ];

    public static function userHasAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperadmin') && $user->isSuperadmin()) {
            return true;
        }

        if (method_exists($user, 'isActiveMusyrif') && $user->isActiveMusyrif()) {
            return true;
        }

        return $user->hasAnyRole(self::ROLES);
    }

    public static function userHasManagementAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperadmin') && $user->isSuperadmin()) {
            return true;
        }

        return $user->hasAnyRole(self::MANAGER_ROLES);
    }

    public static function userHasFullSantriScope(?User $user): bool
    {
        return self::userHasManagementAccess($user);
    }

    public static function accessibleSantriIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if (self::userHasFullSantriScope($user)) {
            return Santri::pluck('id')->all();
        }

        // Santri di bawah pengampu/halaqoh atau asrama (musyrif).
        $scopedHalaqoh = static::scopedHalaqohSantriIds($user);
        $scopedAsrama = static::scopedAsramaSantriIds($user);
        $scopedIds = array_values(array_unique(array_merge($scopedHalaqoh, $scopedAsrama)));
        if ($scopedIds) {
            return $scopedIds;
        }

        // Fallback: gunakan logika akses tahfizh hadits (halaqoh/pengampu) jika tersedia.
        if (class_exists(TahfizhHadits::class)) {
            return TahfizhHadits::accessibleSantriIds($user);
        }

        return [];
    }

    /**
     * Ambil santri berdasarkan halaqoh yang diampu (opsi kelas/asrama bisa dikembangkan).
     */
    protected static function scopedHalaqohSantriIds(User $user): array
    {
        try {
            if (! Schema::hasTable('halaqoh') || ! Schema::hasTable('halaqoh_santri')) {
                return [];
            }

            $guruId = $user->guru->id ?? null;
            if (! $guruId) {
                return [];
            }

            return DB::table('halaqoh')
                ->join('halaqoh_santri', 'halaqoh.id', '=', 'halaqoh_santri.halaqoh_id')
                ->where('halaqoh.guru_id', $guruId)
                ->pluck('halaqoh_santri.santri_id')
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil santri berdasarkan asrama yang diampu musyrif aktif.
     */
    protected static function scopedAsramaSantriIds(User $user): array
    {
        try {
            if (! Schema::hasTable('musyrif_assignments') || ! Schema::hasTable('santri')) {
                return [];
            }

            $guruId = $user->guru->id ?? null;
            if (! $guruId) {
                return [];
            }

            $asramaIds = MusyrifAssignment::query()
                ->active()
                ->where('guru_id', $guruId)
                ->pluck('asrama_id')
                ->filter()
                ->unique()
                ->all();

            if (empty($asramaIds)) {
                return [];
            }

            return Santri::query()
                ->whereIn('asrama_id', $asramaIds)
                ->pluck('id')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
