<?php

namespace App\Filament\Widgets;

use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class KesehatanTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Kasus Kesehatan Mingguan';

    protected static ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $labels = [];
        $counts = [];
        $user = auth()->user();

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->shortDayName;

            $query = SantriHealthLog::query();
            KesehatanScope::applyUnitFilter($query);
            KesehatanScope::applyGenderFilter($query, $user?->kesehatanGenderScope());

            if (! $user?->hasKesehatanFullAccess()) {
                $guruId = $user?->linked_guru_id ?? $user?->ensureLinkedGuruId($user?->name);
                if ($guruId) {
                    $query->where('reporter_id', $guruId);
                } else {
                    $query->whereNull('id');
                }
            }

            $counts[] = $query->whereDate('tanggal_sakit', $day)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kasus',
                    'data' => $counts,
                    'backgroundColor' => '#f87171',
                    'borderColor' => '#dc2626',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
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
