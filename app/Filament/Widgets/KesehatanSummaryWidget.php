<?php

namespace App\Filament\Widgets;

use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class KesehatanSummaryWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    // Longgar supaya Livewire bisa mengisi '' dari query string, kita casting manual di getCards.
    public $filterYear = null;
    public $filterMonth = null;
    public $filterAsramaId = null;

    public function hydrate(): void
    {
        // Pastikan summary diperbarui saat filter (year/month/asrama) berubah.
        $this->cachedStats = null;
    }

    protected function getCards(): array
    {
        $user = auth()->user();
        $reqYear = $this->filterYear !== null ? (int) $this->filterYear : request()->integer('year');
        $reqMonth = $this->filterMonth !== null ? (int) $this->filterMonth : request()->integer('month');
        $asramaId = $this->filterAsramaId !== null ? (int) $this->filterAsramaId : request()->integer('asrama_id');

        $query = $this->baseQuery($asramaId);
        if ($reqYear) {
            $query->whereYear('tanggal_sakit', $reqYear);
        }
        if ($reqMonth) {
            $query->whereMonth('tanggal_sakit', $reqMonth);
        }

        $today = Carbon::today();
        $activeCount = (clone $query)->whereIn('status', ['menunggu', 'ditangani'])->count();
        $dirujuk = (clone $query)->where('status', 'dirujuk')->count();
        $todayCount = (clone $query)->whereDate('tanggal_sakit', $today)->count();

        return [
            Card::make('Kasus Aktif', $activeCount)
                ->description('Menunggu / Ditangani')
                ->descriptionIcon('heroicon-m-heart')
                ->color($activeCount ? 'danger' : 'success'),
            Card::make('Kasus Dirujuk', $dirujuk)
                ->description('Perlu pantauan lanjut')
                ->descriptionIcon('heroicon-m-truck')
                ->color($dirujuk ? 'warning' : 'success'),
            Card::make('Kasus Hari Ini', $todayCount)
                ->description($today->translatedFormat('d M Y'))
                ->descriptionIcon('heroicon-m-calendar-days'),
        ];
    }

    protected function baseQuery(?int $asramaId = null): Builder
    {
        $user = auth()->user();

        $query = SantriHealthLog::query();
        KesehatanScope::applyUnitFilter($query);
        KesehatanScope::applyGenderFilter($query, $user?->kesehatanGenderScope());
        if ($asramaId) {
            $query->where('asrama_id', $asramaId);
        }

        if (! $user?->hasKesehatanFullAccess()) {
            $guruId = $user?->linked_guru_id ?? $user?->ensureLinkedGuruId($user?->name);

            $query->where('reporter_id', $guruId ?: 0);
        }

        return $query;
    }
}
