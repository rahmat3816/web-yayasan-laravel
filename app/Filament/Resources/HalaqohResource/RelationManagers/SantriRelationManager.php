<?php

namespace App\Filament\Resources\HalaqohResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SantriRelationManager extends RelationManager
{
    protected static string $relationship = 'santri';
    protected static ?string $title = 'Santri';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('santri_id')
                    ->label('Santri')
                    ->relationship('santri', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Santri')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nisy')
                    ->label('NISY')
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Santri')
                    ->preloadRecordSelect()
                    ->recordSelect(function (Forms\Components\Select $select) {
                        return $select->searchable()->preload();
                    })
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $owner = $this->getOwnerRecord();
                        $gender = $owner?->guru?->jenis_kelamin;
                        return $query
                            ->when($owner?->unit_id, fn ($q) => $q->where('unit_id', $owner->unit_id))
                            ->when($gender, fn ($q) => $q->where('jenis_kelamin', strtoupper($gender)))
                            ->where(fn ($q) => $q
                                ->whereDoesntHave('halaqoh')
                                ->orWhereHas('halaqoh', fn ($sub) => $sub->where('halaqoh.id', $owner->id))
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
