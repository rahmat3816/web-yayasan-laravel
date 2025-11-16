<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KeamananPlaceholder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Keamanan (Segera)';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?string $slug = 'keamanan/placeholder';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.keamanan-placeholder';

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
        return 'Modul Keamanan';
    }
}
