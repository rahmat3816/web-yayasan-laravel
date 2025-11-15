<?php

namespace App\Filament\Resources\GuruJabatanResource\Pages;

use App\Filament\Resources\GuruJabatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGuruJabatans extends ManageRecords
{
    protected static string $resource = GuruJabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
