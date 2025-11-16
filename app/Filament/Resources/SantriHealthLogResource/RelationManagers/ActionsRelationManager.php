<?php

namespace App\Filament\Resources\SantriHealthLogResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    protected static ?string $label = 'Intervensi';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tindakan')
                ->label('Tindakan')
                ->options([
                    'observasi' => 'Observasi',
                    'obat_ringan' => 'Obat Ringan',
                    'rujuk_klinik' => 'Rujuk Klinik',
                    'rujuk_puskesmas' => 'Rujuk Puskesmas',
                    'rujuk_rumahsakit' => 'Rujuk Rumah Sakit',
                    'lainnya' => 'Lainnya',
                ])
                ->default('observasi')
                ->required(),
            Forms\Components\TextInput::make('rujukan_tempat')
                ->label('Tempat Rujukan')
                ->maxLength(150),
            Forms\Components\Textarea::make('catatan')
                ->rows(3),
            Forms\Components\Hidden::make('handled_by')
                ->default(fn () => auth()->id()),
            Forms\Components\DateTimePicker::make('instruksi_at')
                ->default(now())
                ->label('Waktu Instruksi'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('tindakan')
                    ->label('Tindakan'),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label('Petugas')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('rujukan_tempat')
                    ->label('Rujukan')->placeholder('-'),
                Tables\Columns\TextColumn::make('instruksi_at')
                    ->label('Waktu')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('catatan')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->catatan),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => $this->canHandleActions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => $this->canHandleActions()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasKesehatanFullAccess()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasKesehatanFullAccess()),
                ]),
            ]);
    }

    protected function canHandleActions(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasKesehatanFullAccess()) {
            return true;
        }

        return $user->hasRole(['koor_kesehatan_putra', 'koor_kesehatan_putri']);
    }
}
