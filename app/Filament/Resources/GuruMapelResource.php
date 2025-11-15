<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruMapelResource\Pages;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GuruMapelResource extends Resource
{
    protected static ?string $model = GuruMapel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Guru Mapel';
    protected static ?string $navigationGroup = 'Akademik & Kurikulum';
    protected static ?int $navigationSort = 2;
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
                Forms\Components\Select::make('mapel_id')
                    ->label('Mapel')
                    ->relationship('mapel', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('guru_id', null))
                    ->required(),

                Forms\Components\Select::make('guru_id')
                    ->label('Guru')
                    ->options(fn (Get $get) => static::getGuruOptions($get('unit_id')))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('unit_id')))
                    ->hint(fn (Get $get) => $get('unit_id') ? null : 'Pilih unit terlebih dahulu')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mapel.nama')
                    ->label('Mapel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('guru.nama')
                    ->label('Guru')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mapel_id')
                    ->label('Mapel')
                    ->relationship('mapel', 'nama'),
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),
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
            'index' => Pages\ManageGuruMapels::route('/'),
        ];
    }

    protected static function getGuruOptions($unitId): array
    {
        $unitId = $unitId ? (int) $unitId : null;

        $unitScope = static::resolveUnitScope($unitId);

        if (empty($unitScope)) {
            return [];
        }

        return Guru::query()
            ->whereIn('unit_id', $unitScope)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    protected static function resolveUnitScope(?int $unitId): array
    {
        if (!$unitId) {
            return [];
        }

        foreach (static::getUnitSharingGroups() as $group) {
            if (in_array($unitId, $group, true)) {
                return $group;
            }
        }

        return [$unitId];
    }

    /**
     * @return array<int, array<int>>
     */
    protected static function getUnitSharingGroups(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $groupDefinitions = [
            [
                'Pondok Pesantren As-Sunnah',
                'MTS As-Sunnah',
                'MA As-Sunnah',
            ],
        ];

        $allNames = collect($groupDefinitions)->flatten()->unique()->all();

        $unitIdsByName = Unit::query()
            ->whereIn('nama_unit', $allNames)
            ->pluck('id', 'nama_unit');

        $units = Unit::query()->get(['id', 'nama_unit']);

        $normalize = fn ($name) => Str::lower($name);

        $pondokId = optional($units->first(fn ($unit) => Str::contains($normalize($unit->nama_unit), 'pondok pesantren as-sunnah')))->id;

        $sharingIds = $units
            ->filter(fn ($unit) => Str::contains($normalize($unit->nama_unit), ['mts as-sunnah', 'ma as-sunnah']))
            ->pluck('id')
            ->all();

        $group = collect([$pondokId])
            ->merge($sharingIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $cache = count($group) > 1 ? [$group] : [];

        return $cache;
    }
}
