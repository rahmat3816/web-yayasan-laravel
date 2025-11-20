<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\MutunSetoran;
use App\Models\MutunTarget;
use App\Models\Santri;
use App\Support\TahfizhMutun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class MutunSetoranPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(TahfizhMutun::userHasAccess($user), 403);

        $hasFullScope = TahfizhMutun::userHasFullSantriScope($user);
        $limitedSantriIds = $hasFullScope ? [] : TahfizhMutun::accessibleSantriIds($user);

        $santriQuery = Santri::query()
            ->with([
                'halaqoh' => fn ($query) => $query->select('halaqoh.id', 'nama_halaqoh'),
                'unit:id,nama_unit',
            ])
            ->withCount('mutunTargets')
            ->whereIn('unit_id', TahfizhMutun::unitIds());

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

        $setoranQuery = MutunSetoran::query()
            ->with([
                'target.mutun:id,judul,kitab,nomor,urutan',
                'target.santri:id,nama',
                'penilai:id,nama',
            ])
            ->whereHas('target.santri', function ($query) use ($hasFullScope, $limitedSantriIds) {
                $query->whereIn('unit_id', TahfizhMutun::unitIds());
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

        $targetQuery = MutunTarget::query()
            ->whereHas('santri', function ($query) use ($hasFullScope, $limitedSantriIds) {
                $query->whereIn('unit_id', TahfizhMutun::unitIds());
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
            ? MutunTarget::with('mutun:id,judul,kitab,nomor,urutan')
                ->where('santri_id', $selectedSantriId)
                ->orderByDesc('tahun')
                ->orderByDesc('semester')
                ->orderBy('mutun_id')
                ->get()
            : collect();

        $selectedTargetSummaries = $selectedTargets->isNotEmpty()
            ? $selectedTargets
                ->groupBy(fn (MutunTarget $target) => implode('-', [
                    $target->tahun,
                    $target->semester,
                    $target->mutun?->kitab,
                ]))
                ->map(function ($group) {
                    $ordered = $group->sortBy(fn (MutunTarget $item) => $item->mutun?->nomor ?? $item->mutun?->urutan ?? 0)->values();
                    $first = $ordered->first();
                    $last = $ordered->last();

                    return [
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
                ->values()
            : collect();

        $cardSetoranMap = $this->buildSantriSetoranMap($santriList->pluck('id')->all(), $hasFullScope ? null : $limitedSantriIds);

        return view('filament.pages.setoran-mutun', [
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

        $query = MutunSetoran::query()
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
