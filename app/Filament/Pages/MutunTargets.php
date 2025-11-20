<?php

namespace App\Filament\Pages;

use App\Models\Halaqoh;
use App\Models\Mutun;
use App\Models\MutunTarget;
use App\Models\Santri;
use App\Support\TahfizhMutun;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MutunTargets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Target Mutun';

    protected static ?string $navigationGroup = 'Tahfizh Mutun';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'tahfizh/mutun/targets';

    protected static string $view = 'filament.pages.mutun-targets';

    protected array $payload = [];

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless(TahfizhMutun::userHasManagementAccess($user), 403);

        $filters = [
            'santri_id' => request()->query('filter_santri'),
            'tahun' => request()->query('filter_tahun'),
            'semester' => request()->query('filter_semester'),
        ];

        $santriOptions = Santri::query()
            ->select('id', 'nama')
            ->whereIn('unit_id', TahfizhMutun::unitIds())
            ->orderBy('nama')
            ->get();

        $halaqohOptions = Halaqoh::query()
            ->select('id', 'nama_halaqoh', 'unit_id')
            ->with(['santri:id,nama'])
            ->whereIn('unit_id', TahfizhMutun::unitIds())
            ->orderBy('nama_halaqoh')
            ->get()
            ->map(fn ($halaqoh) => [
                'id' => $halaqoh->id,
                'nama' => $halaqoh->nama_halaqoh,
                'santri' => $halaqoh->santri
                    ->map(fn ($santri) => [
                        'id' => $santri->id,
                        'nama' => $santri->nama,
                    ])
                    ->values(),
            ]);

        $mutunCollection = Mutun::query()
            ->select('id', 'judul', 'kitab', 'nomor', 'urutan')
            ->orderByRaw("CASE WHEN kitab = 'Mutun Tholabul ''Ilmi - Tamhidi' THEN 0 WHEN kitab = 'Mutun Tholabul ''Ilmi - Awal' THEN 1 ELSE 2 END")
            ->orderBy('nomor')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kitab')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'items' => $items->map(function ($mutun) {
                        return [
                            'id' => $mutun->id,
                            'judul' => $mutun->judul,
                            'nomor' => $mutun->nomor ?? $mutun->urutan ?? $mutun->id,
                        ];
                    })->values(),
                ];
            });

        $baseTargetQuery = MutunTarget::with(['santri:id,nama', 'mutun:id,judul,kitab,nomor'])
            ->whereHas('santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()));

        $mutunTargets = (clone $baseTargetQuery)
            ->when($filters['santri_id'], fn ($q) => $q->where('santri_id', $filters['santri_id']))
            ->when($filters['tahun'], fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when($filters['semester'], fn ($q) => $q->where('semester', $filters['semester']))
            ->orderByDesc('tahun')
            ->orderByDesc('semester')
            ->orderBy('santri_id')
            ->orderBy('mutun_id')
            ->get();

        $mutunTargetGroups = $mutunTargets
            ->groupBy(function (MutunTarget $target) {
                return implode('-', [
                    $target->santri_id,
                    $target->tahun,
                    $target->semester,
                    $target->mutun?->kitab,
                ]);
            })
            ->map(function ($group) {
                $sorted = $group->sortBy(fn (MutunTarget $item) => $item->mutun?->nomor ?? $item->mutun?->urutan ?? 0)->values();
                $first = $sorted->first();
                $last = $sorted->last();

                return [
                    'santri' => $first->santri?->nama ?? '-',
                    'tahun' => $first->tahun,
                    'semester' => $first->semester,
                    'kitab' => $first->mutun?->kitab ?? '-',
                    'mutun_awal' => [
                        'nomor' => $first->mutun?->nomor ?? $first->mutun?->urutan,
                        'judul' => $first->mutun?->judul ?? '-',
                    ],
                    'mutun_akhir' => [
                        'nomor' => $last->mutun?->nomor ?? $last->mutun?->urutan,
                        'judul' => $last->mutun?->judul ?? '-',
                    ],
                ];
            })
            ->values();

        $blockedMutunMap = (clone $baseTargetQuery)
            ->get()
            ->groupBy(function (MutunTarget $target) {
                return implode('-', [
                    $target->santri_id,
                    $target->tahun,
                    $target->semester,
                    $target->mutun?->kitab ?? 'unknown',
                ]);
            })
            ->map(fn ($group) => $group->pluck('mutun_id')->values())
            ->toArray();

        $this->payload = [
            'santriOptions' => $santriOptions,
            'halaqohOptions' => $halaqohOptions,
            'yearOptions' => range(now()->year - 1, now()->year + 2),
            'mutunData' => $mutunCollection,
            'mutunTargets' => $mutunTargets,
            'mutunTargetGroups' => $mutunTargetGroups,
            'filterState' => $filters,
            'blockedMutunMap' => $blockedMutunMap,
            'semesterOptions' => [
                'semester_1' => 'Semester 1',
                'semester_2' => 'Semester 2',
            ],
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), $this->payload);
    }

    public function getHeading(): string
    {
        return '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        return TahfizhMutun::userHasManagementAccess(Auth::user());
    }
}
