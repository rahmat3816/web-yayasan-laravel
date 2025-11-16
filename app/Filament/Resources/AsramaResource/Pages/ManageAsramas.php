<?php

namespace App\Filament\Resources\AsramaResource\Pages;

use App\Filament\Resources\AsramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAsramas extends ManageRecords
{
    protected static string $resource = AsramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
