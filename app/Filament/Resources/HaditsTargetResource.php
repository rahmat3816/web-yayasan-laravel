<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HaditsTargetResource\Pages;
use App\Models\Hadits;
use App\Models\HaditsTarget;
use App\Models\Santri;
use App\Support\TahfizhHadits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HaditsTargetResource extends Resource
{
    protected static ?string $model = HaditsTarget::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Tahfizh Hadits';
    protected static ?string $navigationLabel = 'Target Hadits';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'tahfizh/hadits-target-records';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('santri_id')
                    ->label('Santri')
                    ->options(fn () => Santri::whereIn('unit_id', static::allowedUnitIds())
                        ->orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('hadits_id')
                    ->label('Hadits')
                    ->options(fn () => Hadits::orderBy('kitab')->orderBy('urutan')->pluck('judul', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('tahun')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
                Forms\Components\Select::make('semester')
                    ->options([
                        'semester_1' => 'Semester 1',
                        'semester_2' => 'Semester 2',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'belum_mulai' => 'Belum Mulai',
                        'berjalan' => 'Berjalan',
                        'selesai' => 'Selesai',
                    ])
                    ->default('belum_mulai')
                    ->required(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('santri.nama')->label('Santri')->searchable(),
                TextColumn::make('hadits.judul')->label('Hadits')->searchable(),
                TextColumn::make('tahun')->sortable(),
                TextColumn::make('semester')->label('Semester'),
                BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'belum_mulai',
                        'warning' => 'berjalan',
                        'success' => 'selesai',
                    ]),
            ])
            ->filters([
                SelectFilter::make('tahun')->options(
                    collect(range(now()->year + 1, now()->year - 5))->mapWithKeys(fn ($y) => [$y => $y])
                ),
                SelectFilter::make('status')
                    ->options([
                        'belum_mulai' => 'Belum Mulai',
                        'berjalan' => 'Berjalan',
                        'selesai' => 'Selesai',
                    ]),
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
            'index' => Pages\ManageHaditsTargets::route('/'),
        ];
    }

    protected static function allowedUnitIds(): array
    {
        return TahfizhHadits::unitIds();
    }
}
