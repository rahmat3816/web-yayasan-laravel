<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelanggaranTypeResource\Pages;
use App\Models\PelanggaranCategory;
use App\Models\PelanggaranType;
use App\Support\KeamananAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PelanggaranTypeResource extends Resource
{
    protected static ?string $model = PelanggaranType::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?string $navigationLabel = 'Jenis Pelanggaran';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'keamanan/jenis-pelanggaran';

    public static function shouldRegisterNavigation(): bool
    {
        return KeamananAccess::userHasManagementAccess(auth()->user());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategori_id')
                    ->label('Kategori')
                    ->options(fn () => PelanggaranCategory::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Pelanggaran')
                    ->required()
                    ->maxLength(150),
                Forms\Components\Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3),
                Forms\Components\TextInput::make('poin_default')
                    ->label('Poin Default')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Forms\Components\Toggle::make('langsung_sp3')
                    ->label('Langsung SP3')
                    ->helperText('Jika diaktifkan, pelanggaran langsung memicu SP3.'),
                Forms\Components\Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('poin_default')
                    ->label('Poin')
                    ->sortable(),
                Tables\Columns\IconColumn::make('langsung_sp3')
                    ->boolean()
                    ->label('Langsung SP3'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif')->label('Aktif'),
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
            'index' => Pages\ManagePelanggaranTypes::route('/'),
        ];
    }
}
