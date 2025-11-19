<?php

namespace App\Filament\Resources\HaditsSetoranResource\Pages;

use App\Filament\Resources\HaditsSetoranResource;
use Filament\Resources\Pages\ManageRecords;

class ManageHaditsSetorans extends ManageRecords
{
    protected static string $resource = HaditsSetoranResource::class;

    protected static ?string $title = 'Setoran Hadits';
}
