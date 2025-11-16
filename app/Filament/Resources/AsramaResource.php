<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsramaResource\Pages;
use App\Models\Asrama;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AsramaResource extends Resource
{
    protected static ?string $model = Asrama::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Kesantrian';

    protected static ?string $navigationLabel = 'Asrama';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasKesantrianManagementAccess() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Asrama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'putra' => 'Putra',
                        'putri' => 'Putri',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('lokasi')
                    ->label('Lokasi')
                    ->maxLength(150),
                Forms\Components\Textarea::make('keterangan')
                    ->rows(3),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tipe')
                    ->colors([
                        'primary' => 'putra',
                        'success' => 'putri',
                    ])->sortable(),
                Tables\Columns\TextColumn::make('lokasi')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('santri_count')
                    ->counts('santri')
                    ->label('Jumlah Santri')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAsramas::route('/'),
        ];
    }
}
