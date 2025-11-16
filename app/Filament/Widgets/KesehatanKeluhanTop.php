<?php

namespace App\Filament\Widgets;

use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;

class KesehatanKeluhanTop extends ChartWidget
{
    protected static ?string $heading = 'Keluhan Terbanyak (Bulanan)';

    protected static ?int $sort = 3;

    // Longgar agar Livewire bisa mengisi '' dari query string; casting ke int dilakukan di getData.
    public $filterYear = null;
    public $filterMonth = null;
    public $filterAsramaId = null;

    public function hydrate(): void
    {
        // Bersihkan cache data ketika filter berubah.
        $this->cachedData = null;
    }

    protected function getFormSchema(): array
    {
        $years = range(now()->year, now()->year - 2);

        return [
            Forms\Components\Select::make('year')
                ->label('Tahun')
                ->options(collect($years)->mapWithKeys(fn ($y) => [$y => $y]))
                ->default(now()->year)
                ->reactive(),
            Forms\Components\Select::make('month')
                ->label('Bulan')
                ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->setMonth($m)->translatedFormat('F')]))
                ->default(now()->month)
                ->reactive(),
        ];
    }

    protected function getData(): array
    {
        $reqYear = request()->integer('year');
        $reqMonth = request()->integer('month');
        $asramaId = request()->integer('asrama_id');

        $year = $this->filterYear !== null ? (int) $this->filterYear : ($reqYear ?: ($this->filterFormData['year'] ?? now()->year));

        $month = $this->filterMonth;
        if ($month === null) {
            $month = $reqMonth ?: ($this->filterFormData['month'] ?? null);
        }
        $month = $month !== null ? (int) $month : null;
        $asramaId = $this->filterAsramaId !== null ? (int) $this->filterAsramaId : $asramaId;
        $user = auth()->user();

        $query = SantriHealthLog::query()
            ->whereYear('tanggal_sakit', $year);

        if ($month) {
            $query->whereMonth('tanggal_sakit', $month);
        }

        KesehatanScope::applyUnitFilter($query);
        KesehatanScope::applyGenderFilter($query, $user?->kesehatanGenderScope());
        if ($asramaId) {
            $query->where('asrama_id', $asramaId);
        }

        if (! $user?->hasKesehatanFullAccess() && $user?->isActiveMusyrif()) {
            $guruId = $user->linked_guru_id ?? $user->ensureLinkedGuruId($user->name);
            $query->where('reporter_id', $guruId ?: 0);
        }

        $data = $query
            ->selectRaw('keluhan, COUNT(*) as total')
            ->groupBy('keluhan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $labels = $data->pluck('keluhan')->map(fn ($k) => $k ?: 'Tidak diketahui')->all();
        $counts = $data->pluck('total')->all();

        return [
            'datasets' => [
                [
                    'label' => 'Keluhan',
                    'data' => $counts,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
