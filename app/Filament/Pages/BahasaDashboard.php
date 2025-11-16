<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BahasaDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Dashboard Bahasa';

    protected static ?string $navigationGroup = 'Bahasa';

    protected static ?string $slug = 'bahasa/dashboard';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.bahasa-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole([
            'koor_lughoh_putra',
            'koor_lughoh_putri',
            'superadmin',
        ]) ?? false;
    }

    public function getHeading(): string
    {
        return 'Dashboard Bahasa';
    }
}
