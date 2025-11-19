<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Dashboard\TahfizhDashboardController;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerencanaanTahfizh extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = "Target Qur'an";
    protected static ?string $navigationGroup = "Tahfizh Qur'an";
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'tahfizh/perencanaan';
    protected static string $view = 'filament.pages.perencanaan-tahfizh';
    protected array $dashboardData = [];
    protected const NAV_ROLES = [
        'superadmin',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
        'koor_tahfizh_putra',
        'koor_tahfizh_putri',
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

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
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
}
