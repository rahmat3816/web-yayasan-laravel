<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiMapelResource\Pages;
use App\Models\AbsensiMapel;
use App\Models\Guru;
use App\Models\GuruMapel;
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

class AbsensiMapelResource extends Resource
{
    protected static ?string $model = AbsensiMapel::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Absensi Mapel';
    protected static ?string $navigationGroup = 'Akademik & Kurikulum';
    protected static ?int $navigationSort = 4;

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

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'alpha' => 'Alpha',
                    ])
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

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'sakit',
                        'info' => 'izin',
                        'danger' => 'alpha',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'alpha' => 'Alpha',
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
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAbsensiMapels::route('/'),
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

        $allNames = collect($groups)->flatten()->unique()->all();

        $units = Unit::query()
            ->whereIn('nama_unit', $allNames)
            ->get(['id', 'nama_unit']);

        $cache = collect($groups)
            ->map(function (array $names) use ($units) {
                return collect($names)
                    ->map(function ($name) use ($units) {
                        return optional($units->first(fn ($unit) => Str::contains(Str::lower($unit->nama_unit), Str::lower($name))))->id;
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
