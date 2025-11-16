<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SantriPlacementResource\Pages;
use App\Models\Asrama;
use App\Models\Santri;
use App\Support\KesehatanScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SantriPlacementResource extends Resource
{
    protected static ?string $model = Santri::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Kesantrian';

    protected static ?string $navigationLabel = 'Penempatan Santri';

    protected static ?int $navigationSort = 3;

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
                    ->label('Nama Santri')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('asrama_id')
                    ->label('Asrama')
                    ->options(fn () => Asrama::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih asrama tempat santri ditempatkan.'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Santri')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->badge()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->colors([
                        'primary' => 'L',
                        'pink' => 'P',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'P' ? 'Putri' : 'Putra'),
                Tables\Columns\TextColumn::make('asrama.nama')
                    ->label('Asrama')
                    ->placeholder('Belum ditempatkan')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(fn () => \App\Models\Unit::whereIn('nama_unit', KesehatanScope::allowedUnitNames())->pluck('nama_unit', 'id')),
                Tables\Filters\SelectFilter::make('asrama_id')
                    ->label('Asrama')
                    ->relationship('asrama', 'nama')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('asrama_status')
                    ->boolean()
                    ->trueLabel('Sudah ditempatkan')
                    ->falseLabel('Belum ditempatkan')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('asrama_id'),
                        false: fn (Builder $query) => $query->whereNull('asrama_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah Penempatan'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(false),
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
            'index' => Pages\ManageSantriPlacements::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['unit', 'asrama']);

        return KesehatanScope::applyUnitFilter($query, 'self');
    }
}
