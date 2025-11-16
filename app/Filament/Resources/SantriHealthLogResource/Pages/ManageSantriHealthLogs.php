<?php

namespace App\Filament\Resources\SantriHealthLogResource\Pages;

use App\Filament\Resources\SantriHealthLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSantriHealthLogs extends ManageRecords
{
    protected static string $resource = SantriHealthLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Lapor santri sakit')
                ->modalHeading('Lapor santri sakit'),
        ];
    }
}
