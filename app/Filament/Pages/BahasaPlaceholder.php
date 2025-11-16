<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BahasaPlaceholder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Bahasa (Segera)';

    protected static ?string $navigationGroup = 'Bahasa';

    protected static ?string $slug = 'bahasa/placeholder';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.bahasa-placeholder';

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
        return 'Modul Bahasa';
    }
}
