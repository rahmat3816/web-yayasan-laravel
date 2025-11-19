<?php

namespace App\Filament\Pages;

use App\Models\Hadits;
use App\Models\HaditsTarget;
use App\Models\Halaqoh;
use App\Models\Santri;
use App\Support\TahfizhHadits;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HaditsTargetPlanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Target Hadits';

    protected static ?string $navigationGroup = 'Tahfizh Hadits';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'hadits-targets';

    protected static string $view = 'filament.pages.hadits-target-planner';

    protected array $payload = [];

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless(TahfizhHadits::userHasManagementAccess($user), 403);

        $santriIds = TahfizhHadits::accessibleSantriIds($user);
        $hasFullScope = TahfizhHadits::userHasManagementAccess($user);
        $filters = [
            'santri_id' => request()->query('filter_santri'),
            'tahun' => request()->query('filter_tahun'),
            'semester' => request()->query('filter_semester'),
        ];

        $santriOptions = Santri::query()
            ->select('id', 'nama')
            ->whereIn('unit_id', TahfizhHadits::unitIds())
            ->when(! $hasFullScope, fn ($q) => $q->whereIn('id', $santriIds))
            ->orderBy('nama')
            ->get();

        $halaqohOptions = Halaqoh::query()
            ->select('id', 'nama_halaqoh', 'unit_id')
            ->with(['santri:id,nama'])
            ->whereIn('unit_id', TahfizhHadits::unitIds())
            ->when(! $hasFullScope, fn ($q) => $q->whereHas('santri', fn ($sub) => $sub->whereIn('santri.id', $santriIds)))
            ->orderBy('nama_halaqoh')
            ->get()
            ->map(fn ($halaqoh) => [
                'id' => $halaqoh->id,
                'nama' => $halaqoh->nama_halaqoh,
                'santri' => $halaqoh->santri
                    ->when(! $hasFullScope, fn ($collection) => $collection->whereIn('id', $santriIds))
                    ->map(fn ($santri) => [
                        'id' => $santri->id,
                        'nama' => $santri->nama,
                    ])
                    ->values(),
            ]);

        $haditsCollection = Hadits::query()
            ->select('id', 'judul', 'kitab', 'nomor')
            ->orderBy('kitab')
            ->orderBy('nomor')
            ->get()
            ->groupBy('kitab')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'items' => $items->map(function ($hadits) {
                        return [
                            'id' => $hadits->id,
                            'judul' => $hadits->judul,
                            'nomor' => $hadits->nomor,
                        ];
                    })->values(),
                ];
            });

        $baseTargetQuery = HaditsTarget::with(['santri:id,nama', 'hadits:id,judul,kitab,nomor'])
            ->when(! $hasFullScope, fn ($q) => $q->whereIn('santri_id', $santriIds))
            ->when($hasFullScope, fn ($q) => $q->whereHas('santri', fn ($query) => $query->whereIn('unit_id', TahfizhHadits::unitIds())));

        $haditsTargets = (clone $baseTargetQuery)
            ->when($filters['santri_id'], fn ($q) => $q->where('santri_id', $filters['santri_id']))
            ->when($filters['tahun'], fn ($q) => $q->where('tahun', $filters['tahun']))
            ->when($filters['semester'], fn ($q) => $q->where('semester', $filters['semester']))
            ->orderByDesc('tahun')
            ->orderByDesc('semester')
            ->orderBy('santri_id')
            ->orderBy('hadits_id')
            ->get();

        $blockedTargets = (clone $baseTargetQuery)->get();

        $groupedTargets = $haditsTargets
            ->groupBy(function (HaditsTarget $target) {
                return implode('-', [
                    $target->santri_id,
                    $target->tahun,
                    $target->semester,
                    $target->hadits?->kitab,
                ]);
            })
            ->map(function ($group) {
                $sorted = $group->sortBy(fn (HaditsTarget $item) => $item->hadits?->nomor ?? 0)->values();
                $first = $sorted->first();
                $last = $sorted->last();

                return [
                    'santri' => $first->santri?->nama ?? '-',
                    'tahun' => $first->tahun,
                    'semester' => $first->semester,
                    'kitab' => $first->hadits?->kitab ?? '-',
                    'hadits_awal' => [
                        'nomor' => $first->hadits?->nomor,
                        'judul' => $first->hadits?->judul ?? '-',
                    ],
                    'hadits_akhir' => [
                        'nomor' => $last->hadits?->nomor,
                        'judul' => $last->hadits?->judul ?? '-',
                    ],
                ];
            })
            ->values();

        $blockedHaditsMap = $blockedTargets
            ->groupBy(function (HaditsTarget $target) {
                return implode('-', [
                    $target->santri_id,
                    $target->tahun,
                    $target->semester,
                    $target->hadits?->kitab ?? 'unknown',
                ]);
            })
            ->map(fn ($group) => $group->pluck('hadits_id')->values())
            ->toArray();

        $this->payload = [
            'santriOptions' => $santriOptions,
            'halaqohOptions' => $halaqohOptions,
            'yearOptions' => range(now()->year - 1, now()->year + 2),
            'kitabData' => $haditsCollection,
            'haditsTargets' => $haditsTargets,
            'haditsTargetGroups' => $groupedTargets,
            'filterState' => $filters,
            'blockedHaditsMap' => $blockedHaditsMap,
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

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return TahfizhHadits::userHasManagementAccess(Auth::user());
    }
}
