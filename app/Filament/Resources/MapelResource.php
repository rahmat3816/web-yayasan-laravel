<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MapelResource\Pages;
use App\Models\Mapel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MapelResource extends Resource
{
    protected static ?string $model = Mapel::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?string $navigationGroup = 'Akademik & Kurikulum';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Mapel')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'syari' => 'Syari',
                        'umum' => 'Umum',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Mapel')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('tipe')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'syari',
                        'success' => 'umum',
                    ]),

                Tables\Columns\TextColumn::make('guruAssignments')
                    ->label('Unit Ditugaskan')
                    ->state(fn ($record) => $record->guruMapel->loadMissing('unit')->map(fn ($gm) => $gm->unit?->nama_unit ?? '-')->unique()->join(', ') ?: '-')
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'syari' => 'Syari',
                        'umum' => 'Umum',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['superadmin', 'admin'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['superadmin', 'admin'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMapels::route('/'),
        ];
    }
}
