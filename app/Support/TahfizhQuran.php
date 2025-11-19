<?php

namespace App\Support;

use App\Models\Unit;
use App\Models\User;

class TahfizhQuran
{
    protected const CLUSTER_UNITS = [
        'Pondok Pesantren As-Sunnah Gorontalo',
        'MTS As-Sunnah Gorontalo',
        'MA As-Sunnah Limboto Barat',
    ];

    /**
     * Ambil semua unit yang dapat diakses user, termasuk unit tambahan dari jabatan.
     *
     * @param  User|null  $user
     * @return array<int>
     */
    public static function accessibleUnitIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $unitIds = [];

        $expandCluster = function (?int $unitId) {
            if (! $unitId) {
                return [];
            }

            $unit = Unit::find($unitId);

            if (! $unit) {
                return [$unitId];
            }

            $currentName = strtolower($unit->nama_unit ?? '');

            $clusterHit = collect(self::CLUSTER_UNITS)->contains(function ($name) use ($currentName) {
                $target = strtolower($name);

                return $currentName === $target || str_contains($currentName, $target);
            });

            if ($clusterHit) {
                return Unit::whereIn('nama_unit', self::CLUSTER_UNITS)
                    ->pluck('id')
                    ->all();
            }

            return [$unitId];
        };

        if ($user->unit_id) {
            $unitIds = array_merge($unitIds, $expandCluster($user->unit_id));
        }

        $jabatanUnitIds = $user->jabatans()
            ->pluck('guru_jabatan.unit_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($jabatanUnitIds as $jabatanUnitId) {
            $unitIds = array_merge($unitIds, $expandCluster($jabatanUnitId));
        }

        return array_values(array_unique($unitIds));
    }
}
