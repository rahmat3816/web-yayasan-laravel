<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KesantrianDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationLabel = 'Dashboard Kesantrian';

    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'kesantrian-dashboard';

    protected static string $view = 'filament.pages.kesantrian-dashboard';

    protected static array $requiredRoles = [
        'superadmin',
        'admin',
        'admin_unit',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
    ];

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(self::$requiredRoles) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    public function getHeading(): string
    {
        return 'Dashboard Kesantrian';
    }
}
