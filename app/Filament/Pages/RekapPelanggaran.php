<?php

namespace App\Filament\Pages;

use App\Models\PelanggaranCategory;
use App\Models\PelanggaranLog;
use App\Models\PelanggaranSantriStat;
use App\Models\Santri;
use App\Models\Unit;
use App\Support\KeamananAccess;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RekapPelanggaran extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Rekap Pelanggaran';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'keamanan/rekap-pelanggaran';

    protected static string $view = 'filament.pages.rekap-pelanggaran';

    public array $payload = [];

    public function mount(Request $request): void
    {
        abort_unless(KeamananAccess::userHasAccess(auth()->user()), 403);

        $filters = [
            'unit_id' => $request->integer('unit_id') ?: null,
            'kategori_id' => $request->integer('kategori_id') ?: null,
            'tahun' => $request->integer('tahun') ?: now()->year,
        ];

        $this->payload = $this->loadData($filters);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public static function canAccess(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public static function canView(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function getViewData(): array
    {
        return $this->payload;
    }

    protected function loadData(array $filters): array
    {
        $user = auth()->user();
        $accessibleSantriIds = KeamananAccess::accessibleSantriIds($user);
        $hasFullScope = KeamananAccess::userHasFullSantriScope($user);

        $unitFilterIds = $this->expandPondokUnitIds($filters['unit_id']);
        $kategoriId = $filters['kategori_id'];
        $tahun = $filters['tahun'];

        $unitOptions = $this->getUnitOptions($hasFullScope, $accessibleSantriIds);
        $kategoriOptions = PelanggaranCategory::orderBy('nama')->get(['id', 'nama']);

        $logQuery = PelanggaranLog::query()
            ->with(['santri.unit', 'type', 'kategori'])
            ->when(!$hasFullScope, fn (Builder $q) => $q->whereIn('santri_id', $accessibleSantriIds ?: [-1]))
            ->when($unitFilterIds, fn (Builder $q) => $q->whereHas('santri', fn ($s) => $s->whereIn('unit_id', $unitFilterIds)))
            ->when($kategoriId, fn (Builder $q) => $q->where('kategori_id', $kategoriId))
            ->when($tahun, fn (Builder $q) => $q->whereYear('created_at', $tahun));

        $totalLogs = (clone $logQuery)->count();
        $distinctSantri = (clone $logQuery)->distinct('santri_id')->count('santri_id');

        $categoryBreakdown = (clone $logQuery)
            ->selectRaw('kategori_id, count(*) as total, sum(poin) as total_poin')
            ->groupBy('kategori_id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->kategori_id,
                    'nama' => $row->kategori?->nama ?? 'Tidak diketahui',
                    'total' => (int) $row->total,
                    'poin' => (int) $row->total_poin,
                ];
            });

        $trendMonthly = (clone $logQuery)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, count(*) as total, sum(poin) as total_poin')
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $recentLogs = (clone $logQuery)
            ->latest()
            ->limit(10)
            ->get();

        $statQuery = PelanggaranSantriStat::query()
            ->when(!$hasFullScope, fn (Builder $q) => $q->whereIn('santri_id', $accessibleSantriIds ?: [-1]))
            ->when($unitFilterIds, fn (Builder $q) => $q->whereHas('santri', fn ($s) => $s->whereIn('unit_id', $unitFilterIds)));

        $totalPoin = (clone $statQuery)->sum('total_poin');
        $sp1 = (clone $statQuery)->where('sp_level', '>=', 1)->count();
        $sp2 = (clone $statQuery)->where('sp_level', '>=', 2)->count();
        $sp3 = (clone $statQuery)->where('sp_level', '>=', 3)->count();

        return [
            'filters' => $filters,
            'unitOptions' => $unitOptions,
            'kategoriOptions' => $kategoriOptions,
            'stats' => [
                'total_logs' => $totalLogs,
                'distinct_santri' => $distinctSantri,
                'total_poin' => $totalPoin,
                'sp1' => $sp1,
                'sp2' => $sp2,
                'sp3' => $sp3,
            ],
            'categoryBreakdown' => $categoryBreakdown,
            'trendMonthly' => $trendMonthly,
            'recentLogs' => $recentLogs,
        ];
    }

    protected function getUnitOptions(bool $hasFullScope, array $santriIds): array
    {
        if ($hasFullScope) {
            return Unit::orderBy('nama_unit')
                ->get(['id', 'nama_unit'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'nama' => $u->nama_unit,
                ])->all();
        }

        $unitIds = Santri::query()
            ->whereIn('id', $santriIds ?: [-1])
            ->pluck('unit_id')
            ->unique()
            ->all();

        return Unit::whereIn('id', $unitIds ?: [0])
            ->orderBy('nama_unit')
            ->get(['id', 'nama_unit'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->nama_unit,
            ])->all();
    }

    protected function expandPondokUnitIds(?int $unitId): array
    {
        if (! $unitId) {
            return [];
        }

        $unit = Unit::find($unitId);
        if (! $unit) {
            return [$unitId];
        }

        if (str_contains(strtolower($unit->nama_unit), 'pondok pesantren as-sunnah')) {
            return Unit::whereIn('nama_unit', [
                'Pondok Pesantren As-Sunnah Gorontalo',
                'MTS As-Sunnah Gorontalo',
                'MA As-Sunnah Limboto Barat',
            ])->pluck('id')->all();
        }

        return [$unitId];
    }
}
