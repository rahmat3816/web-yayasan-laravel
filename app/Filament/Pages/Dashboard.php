<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Sembunyikan semua widget di dashboard utama.
     * Widget kesehatan tetap tersedia di halaman khusus (Kesantrian / Kesehatan).
     */
    public function getWidgets(): array
    {
        return [];
    }
}
