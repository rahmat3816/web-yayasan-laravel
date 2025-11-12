<?php

namespace App\Filament\Resources\AbsensiMapelResource\Pages;

use App\Filament\Resources\AbsensiMapelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAbsensiMapels extends ManageRecords
{
    protected static string $resource = AbsensiMapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
