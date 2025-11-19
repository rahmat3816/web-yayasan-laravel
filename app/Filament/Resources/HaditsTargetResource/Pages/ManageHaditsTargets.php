<?php

namespace App\Filament\Resources\HaditsTargetResource\Pages;

use App\Filament\Resources\HaditsTargetResource;
use Filament\Resources\Pages\ManageRecords;

class ManageHaditsTargets extends ManageRecords
{
    protected static string $resource = HaditsTargetResource::class;

    protected static ?string $title = 'Target Hadits';
}
