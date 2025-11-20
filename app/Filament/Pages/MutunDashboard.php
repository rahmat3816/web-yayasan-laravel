<?php

namespace App\Filament\Pages;

use App\Models\Mutun;
use App\Models\MutunSetoran;
use App\Models\MutunTarget;
use App\Models\Santri;
use App\Support\TahfizhMutun;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MutunDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Dashboard Mutun';

    protected static ?string $navigationGroup = 'Tahfizh Mutun';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'tahfizh/mutun/dashboard';

    protected static string $view = 'filament.pages.mutun-dashboard';

    public array $stats = [];

    public array $recentSetorans = [];

    public array $santriOptions = [];

    public ?int $selectedSantriId = null;

    public array $lineCharts = [];

    public array $kitabAchievements = [];

    public array $percentageSummary = [];

    public function mount(): void
    {
        abort_unless(TahfizhMutun::userHasManagementAccess(auth()->user()), 403);

        $this->loadSantriOptions();

        if (request()->has('santri_id')) {
            $this->selectedSantriId = (int) request()->query('santri_id', 0);
        } else {
            $firstSantri = collect($this->santriOptions)->first(fn ($option) => $option['id'] !== 0);
            $this->selectedSantriId = $firstSantri['id'] ?? 0;
        }

        $this->loadStats();
        $this->loadRecentSetorans();
        $this->loadLineCharts();
        $this->loadKitabAchievements();
        $this->loadPercentageSummary();
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
        return TahfizhMutun::userHasManagementAccess(auth()->user());
    }

    protected function loadSantriOptions(): void
    {
        $options = Santri::query()
            ->select('id', 'nama')
            ->whereIn('unit_id', TahfizhMutun::unitIds())
            ->orderBy('nama')
            ->get()
            ->map(fn ($santri) => ['id' => $santri->id, 'nama' => $santri->nama])
            ->all();

        array_unshift($options, ['id' => 0, 'nama' => 'Semua Santri']);

        $this->santriOptions = $options;
    }

    protected function loadStats(): void
    {
        $targetsQuery = MutunTarget::query()
            ->whereHas('santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->when($this->selectedSantriId, fn ($q) => $q->where('santri_id', $this->selectedSantriId));

        $setoranQuery = MutunSetoran::query()
            ->whereHas('target.santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->when($this->selectedSantriId, fn ($q) => $q->whereHas('target', fn ($sub) => $sub->where('santri_id', $this->selectedSantriId)));

        $totalTargets = $targetsQuery->count();
        $totalSetorans = $setoranQuery->count();
        $avgMutqin = round((float) (clone $setoranQuery)->whereNotNull('nilai_mutqin')->avg('nilai_mutqin'), 1);

        $this->stats = [
            'total_targets' => $totalTargets,
            'total_setorans' => $totalSetorans,
            'capaian' => $totalTargets > 0 ? round(($totalSetorans / max($totalTargets, 1)) * 100, 1) : 0,
            'avg_mutqin' => $avgMutqin,
        ];
    }

    protected function loadRecentSetorans(): void
    {
        $query = MutunSetoran::query()
            ->with(['target.santri', 'target.mutun', 'penilai'])
            ->whereHas('target.santri', fn ($query) => $query->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->latest('tanggal');

        if ($this->selectedSantriId) {
            $query->whereHas('target', fn ($q) => $q->where('santri_id', $this->selectedSantriId));
        }

        $this->recentSetorans = $query->limit(5)
            ->get()
            ->map(function (MutunSetoran $setoran) {
                return [
                    'tanggal' => optional($setoran->tanggal)->format('d M Y'),
                    'santri' => $setoran->target?->santri?->nama ?? '-',
                    'mutun' => $setoran->target?->mutun?->judul ?? '-',
                    'mutun_nomor' => $setoran->target?->mutun?->nomor,
                    'penilai' => $setoran->penilai?->nama ?? '-',
                    'catatan' => $setoran->catatan,
                ];
            })
            ->all();
    }

    protected function loadLineCharts(): void
    {
        $this->lineCharts = [];

        if (! $this->selectedSantriId) {
            return;
        }

        $setorans = MutunSetoran::query()
            ->with(['target.mutun:id,kitab,nomor'])
            ->whereHas('target', fn ($query) => $query->where('santri_id', $this->selectedSantriId))
            ->orderBy('tanggal')
            ->get();

        $grouped = $setorans->groupBy(fn ($setoran) => $setoran->target?->mutun?->kitab ?? 'Lainnya');

        foreach ($grouped as $kitab => $items) {
            $ordered = $items->sortBy('tanggal');
            $labels = [];
            $data = [];
            $count = 0;

            foreach ($ordered as $setoran) {
                $count++;
                $labels[] = optional($setoran->tanggal)->format('d M') ?? 'Tidak diketahui';
                $data[] = $count;
            }

            $this->lineCharts[] = [
                'kitab' => $kitab,
                'canvas_id' => 'mutun-kitab-chart-' . Str::slug($kitab . '-' . $this->selectedSantriId),
                'labels' => $labels,
                'data' => $data,
            ];
        }
    }

    protected function loadKitabAchievements(): void
    {
        $this->kitabAchievements = [];

        if (! $this->selectedSantriId) {
            return;
        }

        $targetKitabs = MutunTarget::query()
            ->with('mutun:id,kitab')
            ->where('santri_id', $this->selectedSantriId)
            ->get()
            ->groupBy(fn ($target) => $target->mutun?->kitab ?? 'Lainnya');

        if ($targetKitabs->isEmpty()) {
            return;
        }

        $kitabNames = $targetKitabs->keys()->all();

        $mutunPerKitab = Mutun::query()
            ->whereIn('kitab', $kitabNames)
            ->selectRaw('kitab, COUNT(*) as jumlah')
            ->groupBy('kitab')
            ->pluck('jumlah', 'kitab');

        $setoranCounts = MutunSetoran::query()
            ->whereHas('target', fn ($query) => $query->where('santri_id', $this->selectedSantriId))
            ->join('mutun_targets', 'mutun_setorans.target_id', '=', 'mutun_targets.id')
            ->join('mutuns', 'mutuns.id', '=', 'mutun_targets.mutun_id')
            ->selectRaw('mutuns.kitab as kitab, COUNT(*) as jumlah')
            ->groupBy('mutuns.kitab')
            ->pluck('jumlah', 'kitab');

        foreach ($kitabNames as $kitab) {
            $total = $mutunPerKitab[$kitab] ?? 0;
            $completed = $setoranCounts[$kitab] ?? 0;

            $this->kitabAchievements[] = [
                'kitab' => $kitab,
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        }
    }

    protected function loadPercentageSummary(): void
    {
        $this->percentageSummary = [
            'monthly' => [
                'title' => 'Capaian Bulan Ini',
                'label' => now()->translatedFormat('F Y'),
                'value' => $this->calculateSantriSetoranCount('month'),
            ],
            'semester' => [
                'title' => 'Capaian Semester',
                'label' => now()->month <= 6 ? 'Semester 1' : 'Semester 2',
                'value' => $this->calculateSantriSetoranCount('semester'),
            ],
            'year' => [
                'title' => 'Capaian Tahunan',
                'label' => now()->year,
                'value' => $this->calculateSantriSetoranCount('year'),
            ],
        ];
    }

    protected function calculateSantriSetoranCount(string $period): int
    {
        $query = MutunSetoran::query()
            ->whereHas('target.santri', fn ($q) => $q->whereIn('unit_id', TahfizhMutun::unitIds()))
            ->when($this->selectedSantriId, fn ($q) => $q->whereHas('target', fn ($sub) => $sub->where('santri_id', $this->selectedSantriId)));

        $now = Carbon::now();

        if ($period === 'month') {
            $query->whereBetween('tanggal', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        } elseif ($period === 'semester') {
            if ($now->month <= 6) {
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->startOfYear()->addMonths(5)->endOfMonth();
            } else {
                $start = $now->copy()->startOfYear()->addMonths(6);
                $end = $now->copy()->endOfYear();
            }
            $query->whereBetween('tanggal', [$start, $end]);
        } elseif ($period === 'year') {
            $query->whereBetween('tanggal', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
        }

        return (int) $query->count();
    }
}
