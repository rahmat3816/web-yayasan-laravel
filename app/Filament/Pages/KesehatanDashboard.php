<?php

namespace App\Filament\Pages;

use App\Models\Asrama;
use Filament\Pages\Page;

class KesehatanDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Dashboard Kesehatan';

    protected static ?string $navigationGroup = 'Kesehatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'kesehatan/dashboard';

    protected static string $view = 'filament.pages.kesehatan-dashboard';

    // Gunakan tipe longgar agar Livewire bisa mengisi nilai '' dari query string, lalu kita casting manual di widget.
    public $filterYear = null;
    public $filterMonth = null;
    public $filterAsramaId = null;

    protected $queryString = [
        'filterYear' => ['except' => null],
        'filterMonth' => ['except' => null],
        'filterAsramaId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->filterYear ??= now()->year;
        $this->filterMonth ??= now()->month;
        $this->filterAsramaId ??= null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasKesehatanFullAccess() ?? false;
    }

    public function getHeading(): string
    {
        return 'Dashboard Kesehatan';
    }

    public function getViewData(): array
    {
        $years = range(now()->year, now()->year - 2);
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->setMonth($m)->translatedFormat('F')]);
        $asramas = Asrama::orderBy('nama')->pluck('nama', 'id');

        return [
            'years' => $years,
            'months' => $months,
            'asramas' => $asramas,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'filterYear' => $this->filterYear,
            'filterMonth' => $this->filterMonth,
            'filterAsramaId' => $this->filterAsramaId,
        ];
    }
}
