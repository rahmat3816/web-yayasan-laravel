<?php

namespace App\Filament\Resources\PelanggaranTypeResource\Pages;

use App\Filament\Resources\PelanggaranTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePelanggaranTypes extends ManageRecords
{
    protected static string $resource = PelanggaranTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeading(): string
    {
        return 'Jenis Pelanggaran';
    }
}
