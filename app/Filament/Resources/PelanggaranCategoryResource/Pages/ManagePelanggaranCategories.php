<?php

namespace App\Filament\Resources\PelanggaranCategoryResource\Pages;

use App\Filament\Resources\PelanggaranCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePelanggaranCategories extends ManageRecords
{
    protected static string $resource = PelanggaranCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeading(): string
    {
        return 'Kategori Pelanggaran';
    }
}
