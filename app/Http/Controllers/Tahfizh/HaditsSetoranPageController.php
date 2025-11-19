<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\HaditsSetoran;
use App\Models\HaditsTarget;
use App\Models\Santri;
use App\Support\TahfizhHadits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class HaditsSetoranPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(TahfizhHadits::userHasAccess($user), 403);

        $hasFullScope = $user?->hasRole('superadmin') ?? false;
        $limitedSantriIds = $hasFullScope ? [] : TahfizhHadits::accessibleSantriIds($user);

        $santriQuery = Santri::query()
            ->with([
                'halaqoh' => fn ($query) => $query->select('halaqoh.id', 'nama_halaqoh'),
                'unit:id,nama_unit',
            ])
            ->withCount('haditsTargets')
            ->whereIn('unit_id', TahfizhHadits::unitIds());

        if (! $hasFullScope) {
            $santriQuery->whereIn('id', $limitedSantriIds ?: [-1]);
        }

        $santriList = $santriQuery->orderBy('nama')->get();

        $selectedSantriOption = $request->query('santri_id');
        $selectedSantriId = null;

        if ($selectedSantriOption === null) {
            $selectedSantriId = $santriList->first()->id ?? null;
            $selectedSantriFilter = $selectedSantriId ? (string) $selectedSantriId : 'all';
        } elseif ($selectedSantriOption === 'all' || $selectedSantriOption === '') {
            $selectedSantriFilter = 'all';
        } else {
            $selectedSantriId = (int) $selectedSantriOption;

            if (! $hasFullScope && ! in_array($selectedSantriId, $limitedSantriIds, true)) {
                $selectedSantriId = $santriList->first()->id ?? null;
            }

            $selectedSantriFilter = $selectedSantriId ? (string) $selectedSantriId : 'all';
        }

        $setoranQuery = HaditsSetoran::query()
            ->with([
                'target.hadits:id,judul,kitab,nomor',
                'target.santri:id,nama',
                'penilai:id,nama',
            ])
            ->whereHas('target.santri', function ($query) use ($hasFullScope, $limitedSantriIds) {
                $query->whereIn('unit_id', TahfizhHadits::unitIds());
                if (! $hasFullScope) {
                    $query->whereIn('santri.id', $limitedSantriIds ?: [-1]);
                }
            })
            ->when($selectedSantriId, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('santri_id', $selectedSantriId)));

        $recentSetorans = (clone $setoranQuery)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $statsCollection = $setoranQuery->get();

        $targetQuery = HaditsTarget::query()
            ->whereHas('santri', function ($query) use ($hasFullScope, $limitedSantriIds) {
                $query->whereIn('unit_id', TahfizhHadits::unitIds());
                if (! $hasFullScope) {
                    $query->whereIn('santri.id', $limitedSantriIds ?: [-1]);
                }
            })
            ->when($selectedSantriId, fn ($query) => $query->where('santri_id', $selectedSantriId));

        $rekap = [
            'total_setoran' => $statsCollection->count(),
            'target_aktif' => $targetQuery->count(),
            'avg_mutqin' => round((float) $statsCollection->avg('nilai_mutqin'), 1),
        ];

        $selectedTargets = $selectedSantriId
            ? HaditsTarget::with('hadits:id,judul,kitab,nomor')
                ->where('santri_id', $selectedSantriId)
                ->orderByDesc('tahun')
                ->orderByDesc('semester')
                ->orderBy('hadits_id')
                ->get()
            : collect();

        $selectedTargetSummaries = $selectedTargets->isNotEmpty()
            ? $selectedTargets
                ->groupBy(fn (HaditsTarget $target) => implode('-', [
                    $target->tahun,
                    $target->semester,
                    $target->hadits?->kitab,
                ]))
                ->map(function ($group) {
                    $ordered = $group->sortBy(fn (HaditsTarget $item) => $item->hadits?->nomor ?? 0)->values();
                    $first = $ordered->first();
                    $last = $ordered->last();

                    return [
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
                ->values()
            : collect();

        $cardSetoranMap = $this->buildSantriSetoranMap($santriList->pluck('id')->all(), $hasFullScope ? null : $limitedSantriIds);

        return view('filament.pages.setoran-hadits', [
            'santriList' => $santriList,
            'selectedSantriId' => $selectedSantriId,
            'selectedSantriFilter' => $selectedSantriFilter ?? 'all',
            'recentSetorans' => $recentSetorans,
            'rekap' => $rekap,
            'selectedTargets' => $selectedTargets,
            'selectedTargetSummaries' => $selectedTargetSummaries,
            'hasFullScope' => $hasFullScope,
            'cardSetoranMap' => $cardSetoranMap,
        ]);
    }

    protected function buildSantriSetoranMap(array $santriIds, ?array $limitedIds = null): Collection
    {
        if (empty($santriIds)) {
            return collect();
        }

        $query = HaditsSetoran::query()
            ->select('target_id', 'tanggal', 'created_at')
            ->with('target:id,santri_id')
            ->whereHas('target', fn ($q) => $q->whereIn('santri_id', $santriIds));

        if (! empty($limitedIds)) {
            $query->whereHas('target', fn ($q) => $q->whereIn('santri_id', $limitedIds));
        }

        return $query->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn ($setoran) => $setoran->target->santri_id ?? null)
            ->map(fn ($items) => [
                'last_date' => optional($items->first()->tanggal)->format('d M Y'),
                'count' => $items->count(),
            ]);
    }
}
