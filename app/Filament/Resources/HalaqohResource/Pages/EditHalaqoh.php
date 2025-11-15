<?php

namespace App\Filament\Resources\HalaqohResource\Pages;

use App\Filament\Resources\HalaqohResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHalaqoh extends EditRecord
{
    protected static string $resource = HalaqohResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
