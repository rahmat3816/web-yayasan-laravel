<?php

namespace App\Filament\Resources\HaditsResource\Pages;

use App\Filament\Resources\HaditsResource;
use Filament\Resources\Pages\ManageRecords;

class ManageHadits extends ManageRecords
{
    protected static string $resource = HaditsResource::class;

    protected static ?string $title = 'Master Hadits';
}
