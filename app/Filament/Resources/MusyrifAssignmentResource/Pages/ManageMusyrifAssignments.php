<?php

namespace App\Filament\Resources\MusyrifAssignmentResource\Pages;

use App\Filament\Resources\MusyrifAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMusyrifAssignments extends ManageRecords
{
    protected static string $resource = MusyrifAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
