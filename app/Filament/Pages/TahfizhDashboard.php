<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Dashboard\TahfizhDashboardController;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TahfizhDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = "Dashboard Qur'an";

    protected static ?string $navigationGroup = "Tahfizh Qur'an";

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'tahfizh-dashboard';

    protected static string $view = 'filament.pages.tahfizh-dashboard';

    protected array $dashboardData = [];
    protected const NAV_ROLES = [
        'superadmin',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
        'koor_tahfizh_putra',
        'koor_tahfizh_putri',
        'koord_tahfizh_akhwat',
    ];

    public function mount(): void
    {
        abort_unless(static::canView(), 403);

        $controller = app(TahfizhDashboardController::class);
        $response = $controller->index(request());

        if ($response instanceof View) {
            $this->dashboardData = $response->getData();
        }
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), $this->dashboardData);
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(self::NAV_ROLES) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
