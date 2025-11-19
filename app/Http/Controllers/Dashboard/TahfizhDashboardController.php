<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HafalanTarget;
use App\Models\Santri;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TahfizhDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        [$genderFilter, $unitFilter] = $this->resolveFilters($user);
        $selectedSantriId = $request->query('santri_id');

        $totalHalaqoh = DB::table('halaqoh')
            ->when($genderFilter, function ($q) use ($genderFilter) {
                $q->join('halaqoh_santri as hs', 'halaqoh.id', '=', 'hs.halaqoh_id')
                    ->join('santri as sh', 'hs.santri_id', '=', 'sh.id');
                $this->applyGenderFilter($q, 'sh.jenis_kelamin', $genderFilter);
            })
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'halaqoh.unit_id', $unitFilter))
            ->distinct('halaqoh.id')
            ->count('halaqoh.id');

        $totalSantri = DB::table('santri')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter))
            ->count();

        $totalHafalan = DB::table('hafalan_quran as h')
            ->leftJoin('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->count();

        $hafalanPerHalaqoh = DB::table('hafalan_quran')
            ->join('halaqoh', 'hafalan_quran.halaqoh_id', '=', 'halaqoh.id')
            ->when($genderFilter, function ($q) use ($genderFilter) {
                $q->join('santri as hqs', 'hafalan_quran.santri_id', '=', 'hqs.id');
                $this->applyGenderFilter($q, 'hqs.jenis_kelamin', $genderFilter);
            })
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'halaqoh.unit_id', $unitFilter))
            ->select('halaqoh.nama_halaqoh', DB::raw('COUNT(*) as total'))
            ->groupBy('halaqoh.nama_halaqoh')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'nama_halaqoh');

        $hafalanPerSantri = DB::table('hafalan_quran')
            ->join('santri', 'hafalan_quran.santri_id', '=', 'santri.id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'santri.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'santri.unit_id', $unitFilter))
            ->select('santri.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('santri.nama')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'nama');

        $santriCandidates = DB::table('hafalan_quran as h')
            ->join('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->select('s.id', 's.nama', DB::raw('SUM(GREATEST(0, (h.ayah_end - h.ayah_start + 1))) as total_ayat'))
            ->groupBy('s.id', 's.nama')
            ->orderByDesc('total_ayat')
            ->limit(20)
            ->get();

        if (!$selectedSantriId && $santriCandidates->isNotEmpty()) {
            $selectedSantriId = $santriCandidates->first()->id;
        }

        $timelineData = $this->buildTimelineData($selectedSantriId, $genderFilter, $unitFilter);
        $santriTimeline = [
            'labels' => $timelineData['labels'],
            'datasets' => $timelineData['dataset']['data']
                ? [[
                    'label' => $timelineData['dataset']['label'],
                    'data' => $timelineData['dataset']['data'],
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => '#4f46e5',
                    'tension' => 0.3,
                    'fill' => false,
                ]]
                : [],
        ];

        $selectedSantriName = optional($santriCandidates->firstWhere('id', $selectedSantriId))->nama ?? 'Pilih Santri';

        $targetSantriOptions = Santri::query()
            ->select('id', 'nama', 'jenis_kelamin', 'unit_id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter))
            ->orderBy('nama')
            ->get();

        $targetYearOptions = range((int) now()->year - 1, (int) now()->year + 2);

        $hafalanTargets = HafalanTarget::with([
                'santri:id,nama,jenis_kelamin,unit_id',
                'santri.unit:id,nama_unit',
                'creator:id,name',
                'surahStart:id,nama_surah',
                'surahEnd:id,nama_surah',
            ])
            ->whereHas('santri', function ($query) use ($genderFilter, $unitFilter) {
                $query->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
                    ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter));
            })
            ->orderByDesc('tahun')
            ->orderByDesc('updated_at')
            ->get();

        $targetMatrix = [];
        foreach ($hafalanTargets as $target) {
            $targetMatrix[$target->tahun][$target->santri_id] = [
                'juz' => $target->juz,
                'surah_start_id' => $target->surah_start_id,
                'surah_end_id' => $target->surah_end_id,
                'ayat_start' => $target->ayat_start,
                'ayat_end' => $target->ayat_end,
            ];
        }

        $progressChart = $this->buildProgressChart($genderFilter, $unitFilter);
        $percentageSeries = $this->buildPercentageSeries($genderFilter, $unitFilter);
        $actualCoverageSummary = $this->buildActualCoverageSummary($genderFilter, $unitFilter);

        return view('tahfizh.dashboard', compact(
            'totalHalaqoh',
            'totalSantri',
            'totalHafalan',
            'hafalanPerHalaqoh',
            'hafalanPerSantri',
            'santriTimeline',
            'santriCandidates',
            'selectedSantriId',
            'selectedSantriName',
            'genderFilter',
            'unitFilter',
            'targetSantriOptions',
            'targetYearOptions',
            'hafalanTargets',
            'targetMatrix',
            'progressChart',
            'percentageSeries',
            'actualCoverageSummary'
        ));
    }

    public function timeline(Request $request)
    {
        [$genderFilter, $unitFilter] = $this->resolveFilters(Auth::user());
        $santriId = (int) $request->query('santri_id');

        if (!$santriId) {
            return response()->json([
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ]);
        }

        $exists = DB::table('hafalan_quran as h')
            ->join('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->where('s.id', $santriId)
            ->exists();

        if (!$exists) {
            return response()->json([
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ], 404);
        }

        return response()->json($this->buildTimelineData($santriId, $genderFilter, $unitFilter));
    }

    public function storeTarget(Request $request)
    {
        $user = Auth::user();
        [$genderFilter, $unitFilter] = $this->resolveFilters($user);

        $data = $request->validate([
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'juz' => ['required', 'integer', 'between:1,30'],
            'surah_start_id' => ['required', 'integer', 'between:1,114'],
            'surah_end_id' => ['required', 'integer', 'between:1,114'],
            'ayat_start' => ['required', 'integer', 'min:1'],
            'ayat_end' => ['required', 'integer', 'min:1'],
        ]);

        if (!$this->santriWithinScope((int) $data['santri_id'], $genderFilter, $unitFilter)) {
            return back()->withErrors(['santri_id' => 'Santri ini di luar kewenangan Anda.'])->withInput();
        }

        if ($data['surah_end_id'] < $data['surah_start_id']) {
            return back()->withErrors(['surah_end_id' => 'Surat akhir harus setelah surat awal.'])->withInput();
        }

        if ($data['surah_start_id'] === $data['surah_end_id'] && $data['ayat_end'] < $data['ayat_start']) {
            return back()->withErrors(['ayat_end' => 'Ayat akhir tidak boleh lebih kecil dari ayat awal.'])->withInput();
        }

        $summary = $this->summarizeRange(
            (int) $data['juz'],
            (int) $data['surah_start_id'],
            (int) $data['surah_end_id'],
            (int) $data['ayat_start'],
            (int) $data['ayat_end']
        );

        if ($summary['total_ayat'] <= 0) {
            return back()->withErrors(['surah_start_id' => 'Rentang surat/ayat tidak valid untuk juz ini.'])->withInput();
        }

        HafalanTarget::updateOrCreate(
            ['santri_id' => $data['santri_id'], 'tahun' => $data['tahun']],
            [
                'created_by' => $user->id,
                'juz' => $data['juz'],
                'surah_start_id' => $data['surah_start_id'],
                'surah_end_id' => $data['surah_end_id'],
                'ayat_start' => $data['ayat_start'],
                'ayat_end' => $data['ayat_end'],
                'total_ayat' => $summary['total_ayat'],
                'target_per_bulan' => (int) max(1, ceil($summary['total_ayat'] / 12)),
                'target_per_minggu' => (int) max(1, ceil($summary['total_ayat'] / 52)),
                'target_per_hari' => (int) max(1, ceil($summary['total_ayat'] / 365)),
            ]
        );

        return redirect()->route('tahfizh.dashboard')->with('success', 'Target hafalan berhasil disimpan.');
    }

    public function previewTarget(Request $request)
    {
        $data = $request->validate([
            'juz' => ['required', 'integer', 'between:1,30'],
            'surah_start_id' => ['required', 'integer', 'between:1,114'],
            'surah_end_id' => ['required', 'integer', 'between:1,114'],
            'ayat_start' => ['required', 'integer', 'min:1'],
            'ayat_end' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['surah_end_id'] < $data['surah_start_id']) {
            return response()->json(['message' => 'Surat akhir harus setelah surat awal.'], 422);
        }

        $summary = $this->summarizeRange(
            (int) $data['juz'],
            (int) $data['surah_start_id'],
            (int) $data['surah_end_id'],
            (int) $data['ayat_start'],
            (int) $data['ayat_end']
        );

        if ($summary['total_ayat'] <= 0) {
            return response()->json(['message' => 'Rentang tidak valid.'], 422);
        }

        return response()->json([
            'total_juz' => 1,
            'total_halaman' => $summary['total_pages'],
            'total_surah' => $summary['total_surah'],
            'total_ayat' => $summary['total_ayat'],
        ]);
    }

    public function coverageDetail(Santri $santri)
    {
        $user = Auth::user();
        [$genderFilter, $unitFilter] = $this->resolveFilters($user);

        $santriGender = $this->normalizeGender($santri->jenis_kelamin);
        $unitAllowed = $user && $user->hasRole('superadmin')
            ? true
            : $this->unitMatches($unitFilter, $santri->unit_id);

        if (($genderFilter && $santriGender !== $genderFilter) || ! $unitAllowed) {
            abort(403);
        }

        $entries = DB::table('hafalan_quran as h')
            ->leftJoin('quran_surah as qs', 'qs.id', '=', 'h.surah_id')
            ->where('h.santri_id', $santri->id)
            ->whereNotNull('h.surah_id')
            ->whereNotNull('h.ayah_start')
            ->whereNotNull('h.ayah_end')
            ->orderBy('h.surah_id')
            ->orderBy('h.ayah_start')
            ->get(['h.surah_id', 'qs.nama_surah', 'h.ayah_start', 'h.ayah_end', 'h.tanggal_setor']);

        $detail = [];
        foreach ($entries as $entry) {
            $detail[] = [
                'surah' => $entry->nama_surah ?? 'Surah #' . $entry->surah_id,
                'ayat_start' => (int) $entry->ayah_start,
                'ayat_end' => (int) $entry->ayah_end,
                'tanggal_setor' => Carbon::parse($entry->tanggal_setor)->format('d M Y'),
            ];
        }

        return response()->json([
            'santri' => $santri->nama,
            'detail' => $detail,
        ]);
    }

    protected function resolveFilters($user): array
    {
        $genderFilter = null;
        $unitFilter = null;

        if ($user && ($user->hasRole('koordinator_tahfizh_putra') || $user->hasRole('koor_tahfizh_putra'))) {
            $genderFilter = 'L';
        } elseif (
            $user && (
                $user->hasRole('koordinator_tahfizh_putri') ||
                $user->hasRole('koor_tahfizh_putri') ||
                $user->hasRole('koord_tahfizh_akhwat')
            )
        ) {
            $genderFilter = 'P';
        }

        if ($user && ! $user->hasRole('superadmin')) {
            $units = $this->getAccessibleUnitIds($user);
            if (! empty($units)) {
                $unitFilter = count($units) === 1 ? $units[0] : $units;
            }
        }

        return [$genderFilter, $unitFilter];
    }

    protected function genderVariants(string $gender): array
    {
        return $gender === 'P'
            ? ['P', 'p', 'Putri', 'PUTRI', 'Perempuan', 'PEREMPUAN', 'Wanita', 'W']
            : ['L', 'l', 'Putra', 'PUTRA', 'Laki-laki', 'LAKI-LAKI', 'Pria', 'LAKI'];
    }

    protected function applyGenderFilter($query, string $column, ?string $gender)
    {
        if (! $gender) {
            return $query;
        }

        return $query->whereIn($column, $this->genderVariants($gender));
    }

    protected function getAccessibleUnitIds($user): array
    {
        if (! $user?->unit_id) {
            return [];
        }

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

    protected function applyUnitFilter($query, string $column, $unitFilter)
    {
        if (empty($unitFilter)) {
            return $query;
        }

        if (is_array($unitFilter)) {
            return $query->whereIn($column, $unitFilter);
        }

        return $query->where($column, $unitFilter);
    }

    protected function normalizeGender(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $first = strtoupper(substr($value, 0, 1));

        return in_array($first, ['L', 'P'], true) ? $first : null;
    }

    protected function unitMatches($unitFilter, ?int $unitId): bool
    {
        if (empty($unitFilter) || ! $unitId) {
            return true;
        }

        if (is_array($unitFilter)) {
            return in_array($unitId, $unitFilter, true);
        }

        return (int) $unitId === (int) $unitFilter;
    }

    protected function buildTimelineData(?int $santriId, ?string $genderFilter, $unitFilter): array
    {
        if (!$santriId) {
            return [
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ];
        }

        $records = DB::table('hafalan_quran as h')
            ->join('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->where('h.santri_id', $santriId)
            ->orderBy('h.tanggal_setor')
            ->get(['h.tanggal_setor', 'h.ayah_start', 'h.ayah_end', 's.nama']);

        if ($records->isEmpty()) {
            return [
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ];
        }

        $total = 0;
        $points = [];
        foreach ($records as $record) {
            $total += max(0, ($record->ayah_end - $record->ayah_start + 1));
            $points[] = [
                'tanggal' => Carbon::parse($record->tanggal_setor)->format('Y-m-d'),
                'total' => $total,
                'label' => $record->nama,
            ];
        }

        $grouped = collect($points)->groupBy('tanggal')->map(fn ($rows) => $rows->last());

        return [
            'labels' => $grouped->keys()->values()->all(),
            'dataset' => [
                'label' => $records->first()->nama ?? 'Santri',
                'data' => $grouped->pluck('total')->values()->all(),
            ],
        ];
    }

    protected function santriWithinScope(int $santriId, ?string $genderFilter, $unitFilter): bool
    {
        return Santri::query()
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter))
            ->where('id', $santriId)
            ->exists();
    }

    protected function summarizeRange(int $juz, int $surahStart, int $surahEnd, int $ayatStart, int $ayatEnd): array
    {
        $segments = DB::table('quran_juz_map as jm')
            ->join('quran_surah as s', 's.id', '=', 'jm.surah_id')
            ->select('jm.surah_id', 'jm.ayat_awal', 'jm.ayat_akhir', 's.jumlah_ayat')
            ->where('jm.juz', $juz)
            ->whereBetween('jm.surah_id', [$surahStart, $surahEnd])
            ->orderBy('jm.surah_id')
            ->orderBy('jm.ayat_awal')
            ->get();

        if ($segments->isEmpty()) {
            return ['total_ayat' => 0, 'total_surah' => 0, 'total_pages' => 0];
        }

        $totalAyat = 0;
        $surahSet = [];

        foreach ($segments as $segment) {
            $start = $segment->ayat_awal ?: 1;
            $end = $segment->ayat_akhir ?: $segment->jumlah_ayat;

            if ($segment->surah_id == $surahStart) {
                $start = max($start, $ayatStart);
            }
            if ($segment->surah_id == $surahEnd) {
                $end = min($end, $ayatEnd);
            }

            if ($end < $start) {
                continue;
            }

            $totalAyat += ($end - $start + 1);
            $surahSet[$segment->surah_id] = true;
        }

        if ($totalAyat <= 0) {
            return ['total_ayat' => 0, 'total_surah' => 0, 'total_pages' => 0];
        }

        $pages = DB::table('quran_page_map')
            ->where('juz', $juz)
            ->whereBetween('surah_id', [$surahStart, $surahEnd])
            ->get(['page', 'surah_id', 'ayat_awal', 'ayat_akhir'])
            ->filter(function ($row) use ($surahStart, $surahEnd, $ayatStart, $ayatEnd) {
                if ($row->surah_id == $surahStart && $row->ayat_akhir < $ayatStart) {
                    return false;
                }
                if ($row->surah_id == $surahEnd && $row->ayat_awal > $ayatEnd) {
                    return false;
                }
                return true;
            })
            ->pluck('page')
            ->unique()
            ->count();

        return [
            'total_ayat' => $totalAyat,
            'total_surah' => count($surahSet),
            'total_pages' => $pages,
        ];
    }

    protected function calculateTotalAyat(int $juz, int $surahStart, int $surahEnd, int $ayatStart, int $ayatEnd): int
    {
        return $this->summarizeRange($juz, $surahStart, $surahEnd, $ayatStart, $ayatEnd)['total_ayat'];
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

    protected function buildProgressChart(?string $genderFilter, $unitFilter): array
    {
        $targets = HafalanTarget::with('santri:id,nama,unit_id,jenis_kelamin')
            ->whereHas('santri', function ($query) use ($genderFilter, $unitFilter) {
                $query->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
                    ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter));
            })
            ->orderByDesc('tahun')
            ->limit(12)
            ->get();

        if ($targets->isEmpty()) {
            return ['labels' => [], 'target' => [], 'actual' => []];
        }

        $actualMap = DB::table('hafalan_quran as h')
            ->join('santri as s', 's.id', '=', 'h.santri_id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->selectRaw('h.santri_id, YEAR(h.tanggal_setor) as tahun, SUM(GREATEST(0, (h.ayah_end - h.ayah_start + 1))) as total')
            ->groupBy('h.santri_id', 'tahun')
            ->get()
            ->keyBy(fn ($row) => $row->santri_id . '-' . $row->tahun);

        $chart = ['labels' => [], 'target' => [], 'actual' => []];
        foreach ($targets as $target) {
            $key = $target->santri_id . '-' . $target->tahun;
            $chart['labels'][] = ($target->santri->nama ?? 'Santri') . ' - ' . $target->tahun;
            $chart['target'][] = (int) $target->total_ayat;
            $chart['actual'][] = (int) ($actualMap[$key]->total ?? 0);
        }

        return $chart;
    }

    protected function buildPercentageSeries(?string $genderFilter, $unitFilter): array
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $currentMonthKey = sprintf('%04d-%02d', $currentYear, $currentMonth);
        $currentSemesterIndex = $currentMonth <= 6 ? 1 : 2;
        $currentSemesterKey = sprintf('%04d-S%d', $currentYear, $currentSemesterIndex);

        $series = [
            'monthly' => [
                'current_key' => $currentMonthKey,
                'series' => [],
                'options' => [],
            ],
            'semester' => [
                'current_key' => $currentSemesterKey,
                'series' => [],
                'options' => [],
            ],
            'annual' => [
                'current' => ['labels' => [], 'values' => []],
                'previous' => ['labels' => [], 'values' => []],
            ],
        ];

        $monthlyAggregates = [];
        for ($month = $currentMonth; $month >= 1; $month--) {
            $key = sprintf('%04d-%02d', $currentYear, $month);
            $monthlyAggregates[$key] = [
                'label' => Carbon::create($currentYear, $month, 1)->translatedFormat('F Y'),
                'values' => [],
            ];
        }
        foreach ($monthlyAggregates as $key => $meta) {
            $series['monthly']['options'][] = [
                'key' => $key,
                'label' => $meta['label'],
            ];
        }

        $semesterAggregates = [];
        $semesterYear = $currentYear;
        $semesterIndex = $currentSemesterIndex;
        for ($i = 0; $i < 4; $i++) {
            $key = sprintf('%04d-S%d', $semesterYear, $semesterIndex);
            $semesterAggregates[$key] = [
                'label' => 'Semester ' . ($semesterIndex === 1 ? 'I ' : 'II ') . $semesterYear,
                'range' => $semesterIndex === 1 ? range(1, 6) : range(7, 12),
                'year' => $semesterYear,
                'values' => [],
            ];
            $series['semester']['options'][] = [
                'key' => $key,
                'label' => $semesterAggregates[$key]['label'],
            ];

            $semesterIndex--;
            if ($semesterIndex === 0) {
                $semesterIndex = 2;
                $semesterYear--;
            }
        }

        $targets = HafalanTarget::with('santri:id,nama,jenis_kelamin,unit_id')
            ->whereHas('santri', function ($query) use ($genderFilter, $unitFilter) {
                $query->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 'jenis_kelamin', $genderFilter))
                    ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 'unit_id', $unitFilter));
            })
            ->whereBetween('tahun', [$currentYear - 1, $currentYear])
            ->get();

        if ($targets->isNotEmpty()) {
            $actualByPeriod = DB::table('hafalan_quran as h')
                ->join('santri as s', 's.id', '=', 'h.santri_id')
                ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
                ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
                ->selectRaw('h.santri_id, YEAR(h.tanggal_setor) as tahun, MONTH(h.tanggal_setor) as bulan, SUM(GREATEST(0,(h.ayah_end-h.ayah_start+1))) as total')
                ->groupBy('h.santri_id', 'tahun', 'bulan')
                ->get()
                ->groupBy(fn ($row) => $row->santri_id . '-' . $row->tahun);

            foreach ($targets as $target) {
                $name = $target->santri->nama ?? 'Santri';
                $key = $target->santri_id . '-' . $target->tahun;
                $monthlyTotals = $actualByPeriod[$key] ?? collect();

                if ($target->tahun == $currentYear && !empty($monthlyAggregates)) {
                    $monthlyTarget = max(1, $target->total_ayat / 12);
                    foreach ($monthlyAggregates as $monthKey => &$aggregate) {
                        $monthNum = (int) substr($monthKey, -2);
                        $actual = $monthlyTotals->firstWhere('bulan', $monthNum)->total ?? 0;
                        $aggregate['values'][$name] = round(min(100, ($actual / $monthlyTarget) * 100), 1);
                    }
                    unset($aggregate);
                }

                foreach ($semesterAggregates as $semKey => &$aggregate) {
                    if ((int) $aggregate['year'] !== (int) $target->tahun) {
                        continue;
                    }
                    $semesterTarget = max(1, $target->total_ayat / 2);
                    $actual = $monthlyTotals->whereIn('bulan', $aggregate['range'])->sum('total');
                    $aggregate['values'][$name] = round(min(100, ($actual / $semesterTarget) * 100), 1);
                }
                unset($aggregate);

                $periodKey = $target->tahun == $currentYear ? 'current' : ($target->tahun == $currentYear - 1 ? 'previous' : null);
                if ($periodKey) {
                    $yearActual = $monthlyTotals->sum('total');
                    $yearPercentage = round(min(100, ($yearActual / max(1, $target->total_ayat)) * 100), 1);
                    $series['annual'][$periodKey]['labels'][] = $name;
                    $series['annual'][$periodKey]['values'][] = $yearPercentage;
                }
            }
        }

        $series['monthly']['series'] = $this->finalizePercentageAggregates($monthlyAggregates, [
            'background' => 'rgba(14,165,233,0.8)',
            'border' => 'rgba(14,165,233,1)',
        ]);

        $series['semester']['series'] = $this->finalizePercentageAggregates($semesterAggregates, [
            'background' => 'rgba(249,115,22,0.8)',
            'border' => 'rgba(249,115,22,1)',
        ]);

        return $series;
    }

    protected function finalizePercentageAggregates(array $aggregates, array $style): array
    {
        $series = [];
        foreach ($aggregates as $key => $aggregate) {
            $values = $aggregate['values'] ?? [];
            if (!empty($values)) {
                ksort($values, SORT_NATURAL | SORT_FLAG_CASE);
            }

            $series[$key] = [
                'labels' => array_keys($values),
                'datasets' => [[
                    'label' => 'Capaian (%)',
                    'data' => array_values($values),
                    'backgroundColor' => $style['background'],
                    'borderColor' => $style['border'],
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                ]],
            ];
        }

        return $series;
    }

    protected function buildActualCoverageSummary(?string $genderFilter, $unitFilter): array
    {
        $entries = DB::table('hafalan_quran as h')
            ->join('santri as s', 's.id', '=', 'h.santri_id')
            ->when($genderFilter, fn ($q) => $this->applyGenderFilter($q, 's.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $this->applyUnitFilter($q, 's.unit_id', $unitFilter))
            ->select('h.santri_id', 's.nama', 's.unit_id', 's.jenis_kelamin', 'h.surah_id', 'h.ayah_start', 'h.ayah_end', 'h.halaqoh_id')
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

                if ($start <= 0 || $end <= 0 || $end < $start) {
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
                'halaqoh_id' => $segments->first()->halaqoh_id ?? null,
                'total_juz' => $totalJuz,
                'total_halaman' => $totalHalaman,
                'total_surah' => $totalSurah,
                'total_ayat' => $totalAyat,
            ];
        }

        usort($summaries, fn ($a, $b) => $b['total_ayat'] <=> $a['total_ayat']);

        return $summaries;
    }
}
