<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NilaiResource\Pages;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\Nilai;
use App\Models\Santri;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NilaiResource extends Resource
{
    protected static ?string $model = Nilai::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Nilai';
    protected static ?string $navigationGroup = 'Akademik & Kurikulum';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set) {
                        $set('guru_id', null);
                        $set('mapel_id', null);
                        $set('santri_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('guru_id')
                    ->label('Guru')
                    ->options(fn (Get $get) => static::getGuruOptions($get('unit_id')))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('unit_id')))
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('mapel_id', null))
                    ->hint(fn (Get $get) => $get('unit_id') ? null : 'Pilih unit terlebih dahulu')
                    ->required(),

                Forms\Components\Select::make('mapel_id')
                    ->label('Mata Pelajaran')
                    ->options(fn (Get $get) => static::getMapelOptions($get('unit_id'), $get('guru_id')))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('guru_id')))
                    ->reactive()
                    ->afterStateUpdated(fn (Set $set) => $set('santri_id', null))
                    ->hint(fn (Get $get) => $get('guru_id') ? null : 'Pilih guru terlebih dahulu')
                    ->required(),

                Forms\Components\Select::make('santri_id')
                    ->label('Santri')
                    ->options(fn (Get $get) => static::getSantriOptions($get('unit_id')))
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('unit_id')))
                    ->hint(fn (Get $get) => $get('unit_id') ? null : 'Pilih unit terlebih dahulu')
                    ->required(),

                Forms\Components\TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(now()->year)
                    ->required(),

                Forms\Components\Select::make('semester')
                    ->label('Semester')
                    ->options([
                        '1' => 'Semester 1',
                        '2' => 'Semester 2',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('nilai')
                    ->label('Nilai')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('santri.nama')
                    ->label('Santri')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mapel.nama')
                    ->label('Mapel')
                    ->sortable(),

                Tables\Columns\TextColumn::make('guru.nama')
                    ->label('Guru')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),
                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        '1' => 'Semester 1',
                        '2' => 'Semester 2',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['superadmin', 'admin', 'guru'])),
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
            'index' => Pages\ManageNilais::route('/'),
        ];
    }

    protected static function getGuruOptions($unitId): array
    {
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

    protected static function getMapelOptions($unitId, $guruId): array
    {
        $unitScope = static::resolveUnitScope($unitId);

        if (empty($unitScope) || blank($guruId)) {
            return [];
        }

        return GuruMapel::query()
            ->where('guru_id', $guruId)
            ->whereIn('unit_id', $unitScope)
            ->with('mapel:id,nama')
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->mapel?->id,
                'nama' => $assignment->mapel?->nama,
            ])
            ->filter(fn ($mapel) => filled($mapel['id']) && filled($mapel['nama']))
            ->pluck('nama', 'id')
            ->toArray();
    }

    protected static function getSantriOptions($unitId): array
    {
        $unitScope = static::resolveUnitScope($unitId);

        if (empty($unitScope)) {
            return [];
        }

        return Santri::query()
            ->whereIn('unit_id', $unitScope)
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    protected static function resolveUnitScope($unitId): array
    {
        $unitId = $unitId ? (int) $unitId : null;

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

    protected static function getUnitSharingGroups(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $groups = [
            [
                'Pondok Pesantren As-Sunnah',
                'MTS As-Sunnah',
                'MA As-Sunnah',
            ],
        ];

        $units = Unit::query()->get(['id', 'nama_unit']);

        $cache = collect($groups)
            ->map(function (array $names) use ($units) {
                return collect($names)
                    ->map(function ($name) use ($units) {
                        $lower = Str::lower($name);
                        return optional($units->first(fn ($unit) => Str::contains(Str::lower($unit->nama_unit), $lower)))->id;
                    })
                    ->filter()
                    ->values()
                    ->all();
            })
            ->filter(fn ($group) => count($group) > 1)
            ->values()
            ->all();

        return $cache;

    }
}
