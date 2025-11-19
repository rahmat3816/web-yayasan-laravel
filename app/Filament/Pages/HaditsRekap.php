<?php

namespace App\Filament\Pages;

use App\Models\Guru;
use App\Models\Hadits;
use App\Models\HaditsSetoran;
use App\Models\HaditsTarget;
use App\Models\Santri;
use App\Models\Unit;
use App\Support\TahfizhHadits;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class HaditsRekap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Rekap Hadits';

    protected static ?string $navigationGroup = 'Tahfizh Hadits';

    protected static ?string $slug = 'tahfizh/hadits/rekap';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.hadits-rekap';

    public ?int $unitId = null;

    public ?string $kitab = null;

    public ?int $santriId = null;

    public ?int $tahun = null;

    public ?string $semester = null;

    public array $filters = [];

    public array $stats = [];

    public array $records = [];

    public array $kitabSummary = [];

    public function mount(): void
    {
        abort_unless(TahfizhHadits::userHasAccess(auth()->user()), 403);

        $this->tahun = $this->tahun ?? (int) now()->year;
        $this->unitId = $this->unitId ?? $this->detectDefaultUnitId();
        $this->loadFilters(refreshSantri: true);
        $this->loadData();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return TahfizhHadits::userHasAccess(auth()->user());
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['unitId', 'kitab', 'santriId', 'tahun', 'semester'], true)) {
            if ($propertyName === 'unitId') {
                $this->santriId = null;
                $this->loadFilters(refreshSantri: true);
            }

            $this->loadData();
        }
    }

    protected function loadFilters(bool $refreshSantri = false): void
    {
        $this->filters['units'] = Unit::whereIn('id', TahfizhHadits::unitIds())
            ->orderBy('nama_unit')
            ->pluck('nama_unit', 'id')
            ->toArray();

        $this->filters['kitab'] = Hadits::query()
            ->select('kitab')
            ->whereNotNull('kitab')
            ->orderBy('kitab')
            ->distinct()
            ->pluck('kitab', 'kitab')
            ->toArray();

        $this->filters['semesters'] = [
            'semester_1' => 'Semester 1',
            'semester_2' => 'Semester 2',
        ];

        $this->filters['years'] = collect(range(now()->year + 1, now()->year - 5))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

        if ($refreshSantri || empty($this->filters['santri'] ?? null)) {
            $this->filters['santri'] = Santri::query()
                ->whereIn('unit_id', TahfizhHadits::unitIds())
                ->when($this->unitId, fn ($query) => $query->where('unit_id', $this->unitId))
                ->orderBy('nama')
                ->pluck('nama', 'id')
                ->toArray();
        }
    }

    protected function loadData(): void
    {
        $targets = HaditsTarget::query()
            ->with([
                'santri:id,nama,unit_id',
                'santri.unit:id,nama_unit',
                'hadits:id,judul,kitab',
                'hadits.segments:id,hadits_id',
                'setorans:id,target_id,tanggal,nilai_mutqin',
                'setorans.details:id,setoran_id,segment_id,status',
            ])
            ->whereHas('santri', fn ($query) => $query->whereIn('unit_id', TahfizhHadits::unitIds()))
            ->when($this->unitId, fn ($query) => $query->whereHas('santri', fn ($q) => $q->where('unit_id', $this->unitId)))
            ->when($this->kitab, fn ($query) => $query->whereHas('hadits', fn ($q) => $q->where('kitab', $this->kitab)))
            ->when($this->santriId, fn ($query) => $query->where('santri_id', $this->santriId))
            ->when($this->tahun, fn ($query) => $query->where('tahun', $this->tahun))
            ->when($this->semester, fn ($query) => $query->where('semester', $this->semester))
            ->get();

        $totalTargets = $targets->count();
        $totalSetorans = HaditsSetoran::query()
            ->whereIn('target_id', $targets->pluck('id'))
            ->count();

        $this->stats = [
            'total_targets' => $totalTargets,
            'selesai' => $totalSetorans,
            'capaian' => $totalTargets > 0 ? round(($totalSetorans / $totalTargets) * 100, 1) : 0,
            'rata_mutqin' => round((float) $targets->avg(function (HaditsTarget $target) {
                $values = $target->setorans->pluck('nilai_mutqin')->filter();

                if ($values->isEmpty()) {
                    return null;
                }

                return $values->avg();
            }), 1),
        ];

        $setoranCountsPerKitab = HaditsSetoran::query()
            ->selectRaw('hadits.kitab as kitab, COUNT(*) as jumlah')
            ->join('hadits_targets', 'hadits_targets.id', '=', 'hadits_setorans.target_id')
            ->join('hadits', 'hadits.id', '=', 'hadits_targets.hadits_id')
            ->whereIn('hadits_targets.id', $targets->pluck('id'))
            ->groupBy('hadits.kitab')
            ->pluck('jumlah', 'kitab');

        $haditsPerKitab = Hadits::query()
            ->selectRaw('kitab, COUNT(*) as jumlah')
            ->groupBy('kitab')
            ->pluck('jumlah', 'kitab');

        $this->kitabSummary = $haditsPerKitab
            ->map(function ($totalHadits, $kitab) use ($setoranCountsPerKitab) {
                $completed = $setoranCountsPerKitab[$kitab] ?? 0;
                return [
                    'total' => $totalHadits,
                    'completed' => $completed,
                    'avg_progress' => $totalHadits > 0 ? round(($completed / $totalHadits) * 100, 1) : 0,
                ];
            })
            ->sortKeys()
            ->take(6)
            ->toArray();

        $setorans = HaditsSetoran::query()
            ->with([
                'target:id,santri_id,hadits_id,tahun,semester',
                'target.santri:id,nama,unit_id',
                'target.santri.unit:id,nama_unit',
                'target.hadits:id,judul,kitab,nomor',
                'penilai:id,nama',
            ])
            ->whereHas('target.santri', fn ($query) => $query->whereIn('unit_id', TahfizhHadits::unitIds()))
            ->when($this->unitId, fn ($query) => $query->whereHas('target.santri', fn ($q) => $q->where('unit_id', $this->unitId)))
            ->when($this->kitab, fn ($query) => $query->whereHas('target.hadits', fn ($q) => $q->where('kitab', $this->kitab)))
            ->when($this->santriId, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('santri_id', $this->santriId)))
            ->when($this->tahun, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('tahun', $this->tahun)))
            ->when($this->semester, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('semester', $this->semester)))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $this->records = $setorans
            ->map(function (HaditsSetoran $setoran) {
                return [
                    'santri' => $setoran->target?->santri?->nama ?? '-',
                    'unit' => $setoran->target?->santri?->unit?->nama_unit ?? '-',
                    'hadits' => $setoran->target?->hadits?->judul ?? '-',
                    'kitab' => $setoran->target?->hadits?->kitab ?? '-',
                    'tahun' => $setoran->target?->tahun,
                    'semester' => $setoran->target?->semester,
                    'mutqin' => $setoran->nilai_mutqin,
                    'penilai' => $setoran->penilai?->nama ?? '-',
                    'tanggal' => optional($setoran->tanggal)->format('d M Y'),
                    'nomor' => $setoran->target?->hadits?->nomor,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function calculateProgress(HaditsTarget $target): float
    {
        $totalSegments = $target->hadits?->segments?->count() ?? 0;

        if ($totalSegments === 0) {
            return 0;
        }

        $completedSegments = $target->setorans
            ->flatMap(fn ($setoran) => $setoran->details)
            ->filter(fn ($detail) => $detail->status === 'lulus')
            ->pluck('segment_id')
            ->unique()
            ->count();

        return round(($completedSegments / $totalSegments) * 100, 1);
    }

    protected function detectDefaultUnitId(): ?int
    {
        $user = auth()->user();
        $unitIds = TahfizhHadits::unitIds();

        if ($user?->unit_id && in_array((int) $user->unit_id, $unitIds, true)) {
            return (int) $user->unit_id;
        }

        $guru = $user?->guru;

        if (! $guru && $user?->linked_guru_id) {
            $guru = Guru::find($user->linked_guru_id);
        }

        if (! $guru && $user) {
            $guruId = $user->ensureLinkedGuruId($user->name);
            if ($guruId) {
                $guru = Guru::find($guruId);
            }
        }

        if ($guru && in_array((int) $guru->unit_id, $unitIds, true)) {
            return (int) $guru->unit_id;
        }

        return $unitIds[0] ?? null;
    }
}
