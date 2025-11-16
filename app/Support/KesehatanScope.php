<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class KesehatanScope
{
    public static function allowedUnitNames(): array
    {
        return [
            'Pondok Pesantren As-Sunnah Gorontalo',
            'MTS As-Sunnah Gorontalo',
            'MA As-Sunnah Limboto Barat',
        ];
    }

    public static function applyUnitFilter(Builder $query, string $relation = 'santri'): Builder
    {
        if ($relation === 'self') {
            return $query->whereHas('unit', function (Builder $unitQuery) {
                $unitQuery->whereIn('nama_unit', self::allowedUnitNames());
            });
        }

        return $query->whereHas($relation . '.unit', function (Builder $unitQuery) {
            $unitQuery->whereIn('nama_unit', self::allowedUnitNames());
        });
    }

    public static function applyGenderFilter(Builder $query, ?string $gender): Builder
    {
        if (! $gender) {
            return $query;
        }

        return $query->whereHas('santri', fn (Builder $santriQuery) => $santriQuery->where('jenis_kelamin', $gender));
    }
}
