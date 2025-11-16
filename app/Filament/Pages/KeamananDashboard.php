<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KeamananDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Dashboard Keamanan';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?string $slug = 'keamanan/dashboard';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.keamanan-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole([
            'koor_keamanan_putra',
            'koor_keamanan_putri',
            'superadmin',
        ]) ?? false;
    }

    public function getHeading(): string
    {
        return 'Dashboard Keamanan';
    }
}
