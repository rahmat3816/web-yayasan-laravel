<?php

namespace App\Filament\Resources\HalaqohResource\Pages;

use App\Filament\Resources\HalaqohResource;
use App\Models\Halaqoh;
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

        if ($user && ! $user->hasRole('superadmin') && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }

        return $query;
    }
}
