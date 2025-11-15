<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JabatanResource\Pages;
use App\Models\Jabatan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JabatanResource extends Resource
{
    protected static ?string $model = Jabatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Jabatan';
    protected static ?string $navigationGroup = 'Penugasan & Jabatan';
    protected static ?int $navigationSort = 1;
    protected const NAV_ROLES = ['superadmin', 'admin_unit', 'kepala_madrasah'];

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
                Forms\Components\TextInput::make('nama_jabatan')
                    ->label('Nama Jabatan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignments_count')
                    ->counts('guruAssignments')
                    ->label('Total Penugasan')
                    ->badge(),

                Tables\Columns\TextColumn::make('assignments_summary')
                    ->label('Guru per Unit')
                    ->state(function (Jabatan $record) {
                        return $record->guruAssignments
                            ->loadMissing(['user', 'unit'])
                            ->map(function ($assignment) {
                                $unit = $assignment->unit?->nama_unit ?? 'Unit #' . $assignment->unit_id;
                                $name = $assignment->user?->name ?? 'User #' . $assignment->user_id;
                                return "{$name} ({$unit})";
                            })->join(', ') ?: '-';
                    })
                    ->wrap(),
            ])
            ->filters([])
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
            ->defaultSort('nama_jabatan');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJabatans::route('/'),
        ];
    }
}
