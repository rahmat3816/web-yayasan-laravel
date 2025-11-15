<?php

namespace App\Http\Controllers\Program;

use App\Http\Controllers\Controller;
use App\Models\HafalanTarget;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TahfizhQuranProgramController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $unitScope = $this->resolveUnitScope($user);
        $linkedGuruId = $user->linked_guru_id
            ?? (method_exists($user, 'ensureLinkedGuruId') ? $user->ensureLinkedGuruId() : null);

        $canSwitchGuru = $user->hasAnyRole([
            'superadmin',
            'admin',
            'admin_unit',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
        ]);

        $guruOptions = $canSwitchGuru ? $this->fetchGuruOptions($unitScope) : collect();

        $selectedGuruId = $canSwitchGuru
            ? (int) $request->query('guru_id', $linkedGuruId ?: 0)
            : ($linkedGuruId ?: 0);

        if ($canSwitchGuru) {
            if ($selectedGuruId && !$guruOptions->contains(fn ($g) => (int) $g->id === $selectedGuruId)) {
                $selectedGuruId = 0;
            }

            if (!$selectedGuruId && $guruOptions->isNotEmpty()) {
                $selectedGuruId = (int) $guruOptions->first()->id;
            }
        }

        $rangeDays = (int) $request->query('range', 30);
        $rangeDays = max(7, min(120, $rangeDays));
        $rangeOptions = [14, 30, 60, 90, 120];

        if (!$selectedGuruId) {
            return view('programs.tahfizh-quran', [
                'canSwitchGuru' => $canSwitchGuru,
                'guruOptions' => $guruOptions,
                'selectedGuruId' => null,
                'selectedGuruName' => null,
                'rangeDays' => $rangeDays,
                'rangeOptions' => $rangeOptions,
                'statusMessage' => 'Belum ada guru yang terhubung dengan akun ini.',
                'targetRows' => collect(),
                'progressRows' => collect(),
                'coverageSummary' => collect(),
                'halaqohBlocks' => collect(),
                'setoranLatestPaginated' => null,
                'santriInfoMap' => collect(),
                'metrics' => [
                    'total_santri' => 0,
                    'total_target_ayat' => 0,
                    'total_actual_ayat' => 0,
                    'average_progress' => 0,
                    'setoran_count' => 0,
                ],
            ]);
        }

        $guruProfile = DB::table('guru')
            ->select('id', 'nama')
            ->where('id', $selectedGuruId)
            ->first();

        $santriList = $this->fetchSantriByGuru($selectedGuruId, $unitScope);
        if ($santriList->isEmpty()) {
            return view('programs.tahfizh-quran', [
                'canSwitchGuru' => $canSwitchGuru,
                'guruOptions' => $guruOptions,
                'selectedGuruId' => $selectedGuruId,
                'selectedGuruName' => $guruProfile->nama ?? 'Guru',
                'rangeDays' => $rangeDays,
                'rangeOptions' => $rangeOptions,
                'statusMessage' => 'Belum ada santri yang terhubung pada halaqoh guru ini.',
                'targetRows' => collect(),
                'progressRows' => collect(),
                'coverageSummary' => collect(),
                'halaqohBlocks' => collect(),
                'setoranLatestPaginated' => null,
                'santriInfoMap' => collect(),
                'metrics' => [
                    'total_santri' => 0,
                    'total_target_ayat' => 0,
                    'total_actual_ayat' => 0,
                    'average_progress' => 0,
                    'setoran_count' => 0,
                ],
            ]);
        }

        $santriIds = $santriList->pluck('id')->unique()->values();
        $santriInfoMap = $santriList->keyBy('id');
        $halaqohMap = $santriList->mapWithKeys(fn ($row) => [$row->id => $row->nama_halaqoh]);

        $currentTargets = HafalanTarget::with('santri:id,nama')
            ->whereIn('santri_id', $santriIds)
            ->orderByDesc('tahun')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('santri_id')
            ->map(fn ($rows) => $rows->first());

        $surahMap = DB::table('quran_surah')->pluck('nama_surah', 'id');

        $targetRows = $currentTargets->map(function (HafalanTarget $target) use ($santriInfoMap, $surahMap) {
            $santri = $santriInfoMap->get($target->santri_id);

            return [
                'santri_id' => $target->santri_id,
                'santri' => optional($target->santri)->nama ?? ($santri->nama ?? 'Santri'),
                'halaqoh' => $santri->nama_halaqoh ?? '-',
                'tahun' => $target->tahun,
                'juz' => $target->juz,
                'surah_awal' => $surahMap[$target->surah_start_id] ?? ('Surah #' . $target->surah_start_id),
                'surah_akhir' => $surahMap[$target->surah_end_id] ?? ('Surah #' . $target->surah_end_id),
                'ayat_awal' => $target->ayat_start,
                'ayat_akhir' => $target->ayat_end,
                'total_ayat' => $target->total_ayat,
            ];
        })->values();

        $coverageSummary = collect($this->buildCoverageSummary($santriIds));
        $coverageLookup = $coverageSummary->keyBy('santri_id');

        $progressRows = $currentTargets->map(function (HafalanTarget $target) use ($santriInfoMap, $coverageLookup) {
            $santri = $santriInfoMap->get($target->santri_id);
            $actualAyat = (int) ($coverageLookup[$target->santri_id]['total_ayat'] ?? 0);
            $targetAyat = (int) ($target->total_ayat ?? 0);
            $progress = $targetAyat > 0 ? round(min(100, ($actualAyat / $targetAyat) * 100), 1) : 0;

            return [
                'santri_id' => $target->santri_id,
                'santri' => optional($target->santri)->nama ?? ($santri->nama ?? 'Santri'),
                'halaqoh' => $santri->nama_halaqoh ?? '-',
                'target_ayat' => $targetAyat,
                'actual_ayat' => $actualAyat,
                'progress' => $progress,
            ];
        })->values();
        $progressLookup = $progressRows->keyBy('santri_id');
        $targetLookup = $targetRows->keyBy('santri_id');

        $setoranHistory = $this->fetchSetoranHistory($santriIds, $rangeDays);
        $setoranLatestPaginated = $setoranHistory['latest'];

        $halaqohBlocks = $santriList
            ->groupBy('halaqoh_id')
            ->map(function (Collection $rows) use ($setoranHistory, $progressLookup, $targetLookup, $coverageLookup) {
                $halaqohName = $rows->first()->nama_halaqoh ?? 'Halaqoh';

                $santri = $rows->map(function ($row) use ($setoranHistory, $progressLookup, $targetLookup, $coverageLookup) {
                    $santriId = $row->id;
                    return [
                        'id' => $santriId,
                        'nama' => $row->nama,
                        'unit' => $row->nama_unit,
                        'riwayat' => ($setoranHistory['by_santri'][$santriId] ?? collect())->take(10),
                        'progress' => $progressLookup[$santriId]['progress'] ?? null,
                        'target' => $targetLookup[$santriId] ?? null,
                        'coverage' => $coverageLookup[$santriId] ?? null,
                    ];
                });

                return [
                    'nama' => $halaqohName,
                    'santri' => $santri,
                ];
            })
            ->values();

        $metrics = [
            'total_santri' => $santriIds->count(),
            'total_target_ayat' => (int) $targetRows->sum('total_ayat'),
            'total_actual_ayat' => (int) $coverageSummary->sum('total_ayat'),
            'average_progress' => $progressRows->isNotEmpty() ? round($progressRows->avg('progress'), 1) : 0,
            'setoran_count' => $setoranHistory['total'],
        ];

        return view('programs.tahfizh-quran', [
            'canSwitchGuru' => $canSwitchGuru,
            'guruOptions' => $guruOptions,
            'selectedGuruId' => $selectedGuruId,
            'selectedGuruName' => $guruProfile->nama ?? 'Guru',
            'rangeDays' => $rangeDays,
            'rangeOptions' => $rangeOptions,
            'statusMessage' => null,
            'targetRows' => $targetRows,
            'progressRows' => $progressRows,
            'coverageSummary' => $coverageSummary,
            'halaqohBlocks' => $halaqohBlocks,
            'setoranLatestPaginated' => $setoranLatestPaginated,
            'santriInfoMap' => $santriInfoMap,
            'metrics' => $metrics,
        ]);
    }

    protected function resolveUnitScope($user): ?int
    {
        if (!$user) {
            return null;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return null;
        }

        if ($user->hasRole('admin_unit') && $user->unit_id) {
            return (int) $user->unit_id;
        }

        if ($user->hasAnyRole(['koordinator_tahfizh_putra', 'koordinator_tahfizh_putri', 'guru', 'wali_kelas']) && $user->unit_id) {
            return (int) $user->unit_id;
        }

        return $user->unit_id ? (int) $user->unit_id : null;
    }

    protected function fetchGuruOptions(?int $unitScope = null): Collection
    {
        return DB::table('guru as g')
            ->join('halaqoh as h', 'h.guru_id', '=', 'g.id')
            ->select('g.id', 'g.nama')
            ->when($unitScope, fn ($q) => $q->where('h.unit_id', $unitScope))
            ->distinct()
            ->orderBy('g.nama')
            ->get();
    }

    protected function fetchSantriByGuru(int $guruId, ?int $unitScope = null): Collection
    {
        return DB::table('halaqoh as h')
            ->join('halaqoh_santri as hs', 'hs.halaqoh_id', '=', 'h.id')
            ->join('santri as s', 's.id', '=', 'hs.santri_id')
            ->leftJoin('units as u', 'u.id', '=', 's.unit_id')
            ->select(
                's.id',
                's.nama',
                's.unit_id',
                'u.nama_unit',
                'hs.halaqoh_id',
                'h.nama_halaqoh'
            )
            ->where('h.guru_id', $guruId)
            ->when($unitScope, fn ($q) => $q->where('h.unit_id', $unitScope))
            ->orderBy('h.nama_halaqoh')
            ->orderBy('s.nama')
            ->get();
    }

    protected function fetchSetoranHistory(Collection $santriIds, int $rangeDays): array
    {
        if ($santriIds->isEmpty()) {
            return [
                'by_santri' => collect(),
                'latest' => collect(),
                'total' => 0,
            ];
        }

        $query = DB::table('hafalan_quran as h')
            ->leftJoin('quran_surah as qs', 'qs.id', '=', 'h.surah_id')
            ->whereIn('h.santri_id', $santriIds)
            ->select(
                'h.id',
                'h.santri_id',
                'h.tanggal_setor',
                'h.surah_id',
                'qs.nama_surah',
                'h.ayah_start',
                'h.ayah_end',
                'h.juz_start',
                'h.juz_end',
                'h.penilaian_tajwid',
                'h.penilaian_mutqin',
                'h.penilaian_adab',
                'h.catatan'
            );

        if ($rangeDays > 0) {
            $query->where('h.tanggal_setor', '>=', Carbon::now()->subDays($rangeDays)->toDateString());
        }

        $records = $query
            ->orderByDesc('h.tanggal_setor')
            ->orderByDesc('h.id')
            ->get();

        return [
            'by_santri' => $records->groupBy('santri_id'),
            'latest' => $records->take(50),
            'total' => $records->count(),
        ];
    }

    protected function buildCoverageSummary(Collection $santriIds): array
    {
        if ($santriIds->isEmpty()) {
            return [];
        }

        $entries = DB::table('hafalan_quran as h')
            ->join('santri as s', 's.id', '=', 'h.santri_id')
            ->whereIn('h.santri_id', $santriIds)
            ->select('h.santri_id', 's.nama', 'h.surah_id', 'h.ayah_start', 'h.ayah_end')
            ->orderBy('s.nama')
            ->get()
            ->groupBy('santri_id');

        if ($entries->isEmpty()) {
            return [];
        }

        $pageSegments = DB::table('quran_page_map')
            ->get(['page', 'surah_id', 'ayat_awal', 'ayat_akhir'])
            ->groupBy('surah_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'page' => (int) $row->page,
                'start' => (int) ($row->ayat_awal ?: 1),
                'end' => (int) ($row->ayat_akhir ?: $row->ayat_awal ?: 1),
            ]));

        $summaries = [];

        foreach ($entries as $santriId => $segments) {
            $intervalsBySurah = [];

            foreach ($segments as $segment) {
                if (!$segment->surah_id || !$segment->ayah_start || !$segment->ayah_end) {
                    continue;
                }

                $start = min((int) $segment->ayah_start, (int) $segment->ayah_end);
                $end = max((int) $segment->ayah_start, (int) $segment->ayah_end);

                if ($start <= 0 || $end < $start) {
                    continue;
                }

                $intervalsBySurah[$segment->surah_id][] = [$start, $end];
            }

            if (empty($intervalsBySurah)) {
                continue;
            }

            $totalAyat = 0;
            $totalSurah = 0;
            $pagesCovered = [];

            foreach ($intervalsBySurah as $surahId => $intervals) {
                $merged = $this->mergeIntervals($intervals);
                if (empty($merged)) {
                    continue;
                }

                $totalSurah++;

                foreach ($merged as [$start, $end]) {
                    $totalAyat += max(0, $end - $start + 1);

                    foreach ($pageSegments[$surahId] ?? [] as $segment) {
                        $overlapStart = max($start, $segment['start']);
                        $overlapEnd = min($end, $segment['end']);
                        if ($overlapEnd >= $overlapStart) {
                            $pagesCovered[$segment['page']] = true;
                        }
                    }
                }
            }

            if ($totalAyat <= 0) {
                continue;
            }

            $totalHalaman = count($pagesCovered);
            $totalJuz = $totalHalaman ? round($totalHalaman / 20, 2) : 0;

            $summaries[] = [
                'santri_id' => $santriId,
                'santri' => $segments->first()->nama ?? 'Santri #' . $santriId,
                'total_juz' => $totalJuz,
                'total_halaman' => $totalHalaman,
                'total_surah' => $totalSurah,
                'total_ayat' => $totalAyat,
            ];
        }

        usort($summaries, fn ($a, $b) => $b['total_ayat'] <=> $a['total_ayat']);

        return $summaries;
    }

    protected function mergeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [$intervals[0]];

        foreach ($intervals as $current) {
            [$lastStart, $lastEnd] = $merged[count($merged) - 1];
            [$curStart, $curEnd] = $current;
            if ($curStart <= $lastEnd + 1) {
                $merged[count($merged) - 1][1] = max($lastEnd, $curEnd);
            } else {
                $merged[] = $current;
            }
        }

        return $merged;
    }

    protected function paginateCollection(Collection $collection, int $perPage, int $page, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = max(1, $page);
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        );

        $paginator->appends($request->except($pageName));

        return $paginator;
    }
}
