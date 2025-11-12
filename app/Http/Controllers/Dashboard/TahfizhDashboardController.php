<?php
// ==============================
// 📘 Tahap 10.1 – Setup Controller Dashboard
// Tujuan: Membuat semua controller dashboard per role
// ==============================

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ==============================
// TahfizhDashboardController
// ==============================
class TahfizhDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        [$genderFilter, $unitFilter] = $this->resolveFilters($user);
        $selectedSantriId = $request->query('santri_id');

        $totalHalaqoh = DB::table('halaqoh')
            ->when($genderFilter, function ($q) use ($genderFilter) {
                $q->join('guru', 'halaqoh.guru_id', '=', 'guru.id')
                  ->where('guru.jenis_kelamin', $genderFilter);
            })
            ->when($unitFilter, fn($q) => $q->where('halaqoh.unit_id', $unitFilter))
            ->distinct('halaqoh.id')
            ->count('halaqoh.id');

        $totalSantri = DB::table('santri')
            ->when($genderFilter, fn ($q) => $q->where('jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('unit_id', $unitFilter))
            ->count();

        $totalHafalan = DB::table('hafalan_quran as h')
            ->leftJoin('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $q->where('s.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('s.unit_id', $unitFilter))
            ->count();

        $hafalanPerHalaqoh = DB::table('hafalan_quran')
            ->join('halaqoh', 'hafalan_quran.halaqoh_id', '=', 'halaqoh.id')
            ->when($genderFilter, function ($q) use ($genderFilter) {
                $q->join('guru', 'halaqoh.guru_id', '=', 'guru.id')
                  ->where('guru.jenis_kelamin', $genderFilter);
            })
            ->when($unitFilter, fn ($q) => $q->where('halaqoh.unit_id', $unitFilter))
            ->select('halaqoh.nama_halaqoh', DB::raw('COUNT(*) as total'))
            ->groupBy('halaqoh.nama_halaqoh')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'nama_halaqoh');

        $hafalanPerSantri = DB::table('hafalan_quran')
            ->join('santri', 'hafalan_quran.santri_id', '=', 'santri.id')
            ->when($genderFilter, fn ($q) => $q->where('santri.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('santri.unit_id', $unitFilter))
            ->select('santri.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('santri.nama')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'nama');

        $santriCandidates = DB::table('hafalan_quran as h')
            ->join('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $q->where('s.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('s.unit_id', $unitFilter))
            ->select('s.id', 's.nama', DB::raw('SUM(GREATEST(0, (h.ayah_end - h.ayah_start + 1))) as total_ayat'))
            ->groupBy('s.id', 's.nama')
            ->orderByDesc('total_ayat')
            ->limit(20)
            ->get();

        if (!$selectedSantriId && $santriCandidates->isNotEmpty()) {
            $selectedSantriId = $santriCandidates->first()->id;
        }

        $timelineData = $this->buildTimelineData($selectedSantriId, $genderFilter, $unitFilter);
        $timelineDatasets = [];
        if (!empty($timelineData['dataset']['data'])) {
            $timelineDatasets[] = [
                'label' => $timelineData['dataset']['label'] ?? 'Santri',
                'data' => $timelineData['dataset']['data'] ?? [],
                'borderColor' => '#4f46e5',
                'backgroundColor' => '#4f46e5',
                'tension' => 0.3,
                'fill' => false,
            ];
        }

        $santriTimeline = [
            'labels' => $timelineData['labels'],
            'datasets' => $timelineDatasets,
        ];

        $selectedSantriName = optional($santriCandidates->firstWhere('id', $selectedSantriId))->nama ?? 'Pilih Santri';

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
            'unitFilter'
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
            ->when($genderFilter, fn ($q) => $q->where('s.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('s.unit_id', $unitFilter))
            ->where('s.id', $santriId)
            ->exists();

        if (! $exists) {
            return response()->json([
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ], 404);
        }

        return response()->json($this->buildTimelineData($santriId, $genderFilter, $unitFilter));
    }

    protected function resolveFilters($user): array
    {
        $genderFilter = null;
        $unitFilter = null;

        if ($user && $user->hasRole('koordinator_tahfizh_putra')) {
            $genderFilter = 'L';
        } elseif ($user && $user->hasRole('koordinator_tahfizh_putri')) {
            $genderFilter = 'P';
        }

        if ($user && !$user->hasRole('superadmin') && $user->unit_id) {
            $unitFilter = $user->unit_id;
        }

        return [$genderFilter, $unitFilter];
    }

    protected function buildTimelineData(?int $santriId, ?string $genderFilter, ?int $unitFilter): array
    {
        if (!$santriId) {
            return [
                'labels' => [],
                'dataset' => ['label' => 'Santri', 'data' => []],
            ];
        }

        $records = DB::table('hafalan_quran as h')
            ->join('santri as s', 'h.santri_id', '=', 's.id')
            ->when($genderFilter, fn ($q) => $q->where('s.jenis_kelamin', $genderFilter))
            ->when($unitFilter, fn ($q) => $q->where('s.unit_id', $unitFilter))
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
            ];
        }

        $grouped = collect($points)->groupBy('tanggal')->map(fn ($rows) => $rows->last()['total']);

        return [
            'labels' => $grouped->keys()->values()->all(),
            'dataset' => [
                'label' => $records->first()->nama ?? 'Santri',
                'data' => $grouped->values()->all(),
            ],
        ];
    }
}
