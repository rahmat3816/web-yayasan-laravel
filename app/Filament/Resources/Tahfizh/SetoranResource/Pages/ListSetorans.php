<?php

namespace App\Filament\Resources\Tahfizh\SetoranResource\Pages;

use App\Filament\Resources\Tahfizh\SetoranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetorans extends ListRecords
{
    protected static string $resource = SetoranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
