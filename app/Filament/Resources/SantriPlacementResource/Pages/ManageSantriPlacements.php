<?php

namespace App\Filament\Resources\SantriPlacementResource\Pages;

use App\Filament\Resources\SantriPlacementResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSantriPlacements extends ManageRecords
{
    protected static string $resource = SantriPlacementResource::class;

    protected static ?string $title = 'Penempatan Santri';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
