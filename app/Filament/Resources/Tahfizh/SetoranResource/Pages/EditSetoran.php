<?php

namespace App\Filament\Resources\Tahfizh\SetoranResource\Pages;

use App\Filament\Resources\Tahfizh\SetoranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetoran extends EditRecord
{
    protected static string $resource = SetoranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
