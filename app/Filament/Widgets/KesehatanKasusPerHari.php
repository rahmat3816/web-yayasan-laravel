<?php

namespace App\Filament\Widgets;

use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class KesehatanKasusPerHari extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Santri Sakit per Hari';

    protected static ?int $sort = 1;

    // Longgar agar Livewire bisa mengisi '' dari query string; kita casting manual di getData.
    public $filterYear = null;
    public $filterMonth = null;
    public $filterAsramaId = null;

    public function hydrate(): void
    {
        // Pastikan data grafik diperbarui saat filter halaman berubah.
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

        $labels = [];
        $data = [];

        $queryBase = SantriHealthLog::query();
        KesehatanScope::applyUnitFilter($queryBase);
        KesehatanScope::applyGenderFilter($queryBase, $user?->kesehatanGenderScope());
        if ($asramaId) {
            $queryBase->where('asrama_id', $asramaId);
        }

        if (! $user?->hasKesehatanFullAccess() && $user?->isActiveMusyrif()) {
            $guruId = $user->linked_guru_id ?? $user->ensureLinkedGuruId($user->name);
            $queryBase->where('reporter_id', $guruId ?: 0);
        }

        if ($month) {
            // Mode bulanan terpilih: tampilkan per hari di bulan itu.
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->toDateString();
                $labels[] = Carbon::create($year, $month, $day)->format('d');
                $data[] = (clone $queryBase)
                    ->whereYear('tanggal_sakit', $year)
                    ->whereDate('tanggal_sakit', $date)
                    ->count();
            }
        } else {
            // Mode semua bulan: tampilkan agregat per bulan Jan–Des di tahun terpilih.
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create($year, $m, 1)->translatedFormat('M');
                $data[] = (clone $queryBase)
                    ->whereYear('tanggal_sakit', $year)
                    ->whereMonth('tanggal_sakit', $m)
                    ->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kasus',
                    'data' => $data,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.2)',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'min' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
