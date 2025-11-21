<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelanggaranCategoryResource\Pages;
use App\Models\PelanggaranCategory;
use App\Support\KeamananAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PelanggaranCategoryResource extends Resource
{
    protected static ?string $model = PelanggaranCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?string $navigationLabel = 'Kategori Pelanggaran';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'keamanan/kategori-pelanggaran';

    public static function shouldRegisterNavigation(): bool
    {
        return KeamananAccess::userHasManagementAccess(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama Kategori'),
                Forms\Components\Textarea::make('deskripsi')
                    ->rows(3)
                    ->label('Deskripsi'),
                Forms\Components\TextInput::make('sp_threshold')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->label('Ambang SP (poin)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sp_threshold')
                    ->label('Ambang SP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePelanggaranCategories::route('/'),
        ];
    }
}
