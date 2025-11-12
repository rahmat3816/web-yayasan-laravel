<?php

namespace App\Filament\Resources\GuruMapelResource\Pages;

use App\Filament\Resources\GuruMapelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGuruMapels extends ManageRecords
{
    protected static string $resource = GuruMapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

