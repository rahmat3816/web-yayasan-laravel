<?php

namespace App\Filament\Pages;

use App\Models\Guru;
use App\Models\Mutun;
use App\Models\MutunSetoran;
use App\Models\MutunTarget;
use App\Models\Santri;
use App\Models\Unit;
use App\Support\TahfizhMutun;
use Filament\Pages\Page;

class RekapMutun extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Rekap Mutun';

    protected static ?string $navigationGroup = 'Tahfizh Mutun';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'tahfizh/mutun/rekap';

    protected static string $view = 'filament.pages.rekap-mutun';

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
        abort_unless(TahfizhMutun::userHasAccess(auth()->user()), 403);

        $this->unitId = $this->unitId ?? $this->detectDefaultUnitId();
        $this->loadFilters(refreshSantri: true);
        $this->loadData();
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
        return TahfizhMutun::userHasAccess(auth()->user());
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
        $this->filters['units'] = Unit::whereIn('id', TahfizhMutun::unitIds())
            ->orderBy('nama_unit')
            ->pluck('nama_unit', 'id')
            ->toArray();

        $this->filters['kitab'] = Mutun::query()
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
                ->whereIn('unit_id', TahfizhMutun::unitIds())
                ->when($this->unitId, fn ($query) => $query->where('unit_id', $this->unitId))
                ->orderBy('nama')
                ->pluck('nama', 'id')
                ->toArray();
        }
    }

    protected function loadData(): void
    {
        $unitFilterIds = $this->resolveUnitFilterIds();

        $targets = MutunTarget::query()
            ->with([
                'santri:id,nama,unit_id',
                'santri.unit:id,nama_unit',
                'mutun:id,judul,kitab,nomor,urutan',
                'setorans:id,target_id,tanggal,nilai_mutqin',
            ])
            ->whereHas('santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->when($unitFilterIds, fn ($query) => $query->whereHas('santri', fn ($q) => $q->whereIn('unit_id', $unitFilterIds)))
            ->when($this->kitab, fn ($query) => $query->whereHas('mutun', fn ($q) => $q->where('kitab', $this->kitab)))
            ->when($this->santriId, fn ($query) => $query->where('santri_id', $this->santriId))
            ->when($this->tahun, fn ($query) => $query->where('tahun', $this->tahun))
            ->when($this->semester, fn ($query) => $query->where('semester', $this->semester))
            ->get();

        $totalTargets = $targets->count();
        $totalSetorans = MutunSetoran::query()
            ->whereIn('target_id', $targets->pluck('id'))
            ->count();

        $this->stats = [
            'total_targets' => $totalTargets,
            'selesai' => $totalSetorans,
            'capaian' => $totalTargets > 0 ? round(($totalSetorans / $totalTargets) * 100, 1) : 0,
            'rata_mutqin' => round((float) $targets->avg(function (MutunTarget $target) {
                $values = $target->setorans->pluck('nilai_mutqin')->filter();

                if ($values->isEmpty()) {
                    return null;
                }

                return $values->avg();
            }), 1),
        ];

        $setoranCountsPerKitab = MutunSetoran::query()
            ->selectRaw('mutuns.kitab as kitab, COUNT(*) as jumlah')
            ->join('mutun_targets', 'mutun_targets.id', '=', 'mutun_setorans.target_id')
            ->join('mutuns', 'mutuns.id', '=', 'mutun_targets.mutun_id')
            ->whereIn('mutun_targets.id', $targets->pluck('id'))
            ->groupBy('mutuns.kitab')
            ->pluck('jumlah', 'kitab');

        $mutunPerKitab = Mutun::query()
            ->selectRaw('kitab, COUNT(*) as jumlah')
            ->groupBy('kitab')
            ->pluck('jumlah', 'kitab');

        $this->kitabSummary = $mutunPerKitab
            ->map(function ($totalMutun, $kitab) use ($setoranCountsPerKitab) {
                $completed = $setoranCountsPerKitab[$kitab] ?? 0;
                return [
                    'total' => $totalMutun,
                    'completed' => $completed,
                    'avg_progress' => $totalMutun > 0 ? round(($completed / $totalMutun) * 100, 1) : 0,
                ];
            })
            ->sortKeys()
            ->take(6)
            ->toArray();

        $setorans = MutunSetoran::query()
            ->with([
                'target:id,santri_id,mutun_id,tahun,semester',
                'target.santri:id,nama,unit_id',
                'target.santri.unit:id,nama_unit',
                'target.mutun:id,judul,kitab,nomor,urutan',
                'penilai:id,nama',
            ])
            ->whereHas('target.santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->when($unitFilterIds, fn ($query) => $query->whereHas('target.santri', fn ($q) => $q->whereIn('unit_id', $unitFilterIds)))
            ->when($this->kitab, fn ($query) => $query->whereHas('target.mutun', fn ($q) => $q->where('kitab', $this->kitab)))
            ->when($this->santriId, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('santri_id', $this->santriId)))
            ->when($this->tahun, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('tahun', $this->tahun)))
            ->when($this->semester, fn ($query) => $query->whereHas('target', fn ($q) => $q->where('semester', $this->semester)))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $this->records = $setorans
            ->map(function (MutunSetoran $setoran) {
                $mutun = $setoran->target?->mutun;
                return [
                    'santri' => $setoran->target?->santri?->nama ?? '-',
                    'unit' => $setoran->target?->santri?->unit?->nama_unit ?? '-',
                    'mutun' => $mutun?->judul ?? '-',
                    'kitab' => $mutun?->kitab ?? '-',
                    'tahun' => $setoran->target?->tahun,
                    'semester' => $setoran->target?->semester,
                    'mutqin' => $setoran->nilai_mutqin,
                    'penilai' => $setoran->penilai?->nama ?? '-',
                    'tanggal' => optional($setoran->tanggal)->format('d M Y'),
                    'nomor' => $mutun?->nomor ?? $mutun?->urutan,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function detectDefaultUnitId(): ?int
    {
        $user = auth()->user();
        $unitIds = TahfizhMutun::unitIds();

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

    protected function resolveUnitFilterIds(): ?array
    {
        if (! $this->unitId) {
            return null;
        }

        $unit = Unit::find($this->unitId);

        if (! $unit) {
            return null;
        }

        $clusterNames = [
            'Pondok Pesantren As-Sunnah Gorontalo',
            'MTS As-Sunnah Gorontalo',
            'MA As-Sunnah Limboto Barat',
        ];

        if (in_array($unit->nama_unit, $clusterNames, true)) {
            return Unit::whereIn('nama_unit', $clusterNames)->pluck('id')->all();
        }

        return [$this->unitId];
    }
}
