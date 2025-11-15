<?php

namespace App\Filament\Resources;

use App\Models\KalenderPendidikan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use App\Filament\Resources\KalenderPendidikanResource\Pages;

class KalenderPendidikanResource extends Resource
{
    protected static ?string $model = KalenderPendidikan::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Kalender Pendidikan';
    protected static ?string $navigationGroup = 'Akademik & Kurikulum';
    protected static ?int $navigationSort = 3;
    protected const NAV_ROLES = ['superadmin', 'admin', 'kepala_madrasah', 'wakamad_kurikulum'];

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(self::NAV_ROLES) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('Unit Pendidikan')
                    ->relationship('unit', 'nama_unit')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(now()->year + 1)
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required(),

                Repeater::make('libur')
                    ->label('Tanggal Libur')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal Libur')
                            ->required(),
                    ])
                    ->columns(1),

                Forms\Components\Textarea::make('event')
                    ->label('Event')
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('libur')
                    ->label('Libur')
                    ->formatStateUsing(fn ($state) => implode(', ', json_decode($state, true) ?? []))
                    ->limit(50),

                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),
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
            ->defaultSort('tahun_ajaran', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKalenderPendidikans::route('/'),
        ];
    }
}
