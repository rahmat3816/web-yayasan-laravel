<?php

namespace App\Filament\Resources\KalenderPendidikanResource\Pages;

use App\Filament\Resources\KalenderPendidikanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKalenderPendidikans extends ManageRecords
{
    protected static string $resource = KalenderPendidikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
