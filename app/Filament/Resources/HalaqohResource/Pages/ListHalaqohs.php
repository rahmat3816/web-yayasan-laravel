<?php

namespace App\Filament\Resources\HalaqohResource\Pages;

use App\Filament\Resources\HalaqohResource;
use App\Models\Halaqoh;
use App\Models\Unit;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListHalaqohs extends ListRecords
{
    protected static string $resource = HalaqohResource::class;
    protected static string $view = 'filament.resources.halaqoh-resource.pages.list-halaqohs';

    public int $totalHalaqoh = 0;

    public int $totalSantri = 0;

    public int $totalPengampu = 0;

    public function mount(): void
    {
        parent::mount();

        $this->hydrateStats();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    protected function hydrateStats(): void
    {
        $query = $this->getUserScopedHalaqohQuery();

        $this->totalHalaqoh = (clone $query)->count();

        $this->totalSantri = (clone $query)
            ->withCount('santri')
            ->get()
            ->sum('santri_count');

        $this->totalPengampu = (clone $query)
            ->whereNotNull('guru_id')
            ->distinct('guru_id')
            ->count('guru_id');
    }

    protected function getUserScopedHalaqohQuery()
    {
        $query = Halaqoh::query();
        $user = auth()->user();

        if ($user && ! $user->hasRole('superadmin')) {
            $unitIds = $this->getAccessibleUnitIds($user);
            if (! empty($unitIds)) {
                $query->whereIn('unit_id', $unitIds);
            }
        }

        return $query;
    }

    protected function getAccessibleUnitIds($user): array
    {
        if (! $user?->unit_id) {
            return [];
        }

        // Jika user berada di unit Pondok, tampilkan juga MTS & MA.
        $unit = Unit::find($user->unit_id);
        if ($unit && str_contains(strtolower($unit->nama_unit), 'pondok pesantren as-sunnah')) {
            return Unit::whereIn('nama_unit', [
                'Pondok Pesantren As-Sunnah Gorontalo',
                'MTS As-Sunnah Gorontalo',
                'MA As-Sunnah Limboto Barat',
            ])->pluck('id')->all();
        }

        return [$user->unit_id];
    }
}
