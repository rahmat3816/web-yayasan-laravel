<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HalaqohResource\Pages;
use App\Filament\Resources\HalaqohResource\RelationManagers;
use App\Models\Guru;
use App\Models\Halaqoh;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HalaqohResource extends Resource
{
    protected static ?string $model = Halaqoh::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Halaqoh';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'tahfizh/halaqoh';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole([
            'superadmin',
            'kabag_kesantrian_putra',
            'kabag_kesantrian_putri',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
            'koor_tahfizh_putra',
            'koor_tahfizh_putri',
        ]) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_halaqoh')
                    ->label('Nama Halaqoh')
                    ->maxLength(100)
                    ->default(fn () => 'Halaqoh ' . now()->format('His'))
                    ->required(),
                Select::make('unit_id')
                    ->label('Unit Pendidikan')
                    ->options(fn () => static::getUnitOptions())
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->default(fn () => auth()->user()?->unit_id)
                    ->disabled(fn () => !static::userIsSuperadmin()),
                Select::make('guru_id')
                    ->label('Guru Pengampu')
                    ->options(fn (Get $get) => static::getGuruOptions(
                        unitId: $get('unit_id') ?: static::getDefaultUnitId(),
                        currentGuruId: $get('guru_id')
                    ))
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_halaqoh')
                    ->label('Halaqoh')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guru.nama')
                    ->label('Guru Pengampu')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('santri_count')
                    ->label('Jumlah Santri')
                    ->counts('santri')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(fn () => static::getUnitOptions())
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SantriRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHalaqohs::route('/'),
            'create' => Pages\CreateHalaqoh::route('/create'),
            'edit' => Pages\EditHalaqoh::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && ! $user->hasRole('superadmin') && $user->unit_id) {
            $query->whereIn('unit_id', static::getAccessibleUnitIds($user));
        }

        return $query;
    }

    protected static function getDefaultUnitId(): ?int
    {
        return auth()->user()?->unit_id;
    }

    protected static function userIsSuperadmin(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('superadmin') ?? false;
    }

    protected static function getGenderScope(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->hasRole('koordinator_tahfizh_putra')) {
            return 'L';
        }

        if ($user->hasRole('koordinator_tahfizh_putri')) {
            return 'P';
        }

        return null;
    }

    protected static function getUnitOptions(): array
    {
        return Unit::query()
            ->when(!static::userIsSuperadmin(), function ($q) {
                $user = auth()->user();
                $unitIds = static::getAccessibleUnitIds($user);
                $q->whereIn('id', $unitIds ?: [0]);
            })
            ->orderBy('nama_unit')
            ->pluck('nama_unit', 'id')
            ->toArray();
    }

    protected static function getGuruOptions(?int $unitId = null, ?int $currentGuruId = null): array
    {
        $gender = static::getGenderScope();
        $unitIds = $unitId ? [$unitId] : static::getAccessibleUnitIds(auth()->user());

        return Guru::query()
            ->select('id', 'nama')
            ->when($unitIds, fn ($q) => $q->whereIn('unit_id', $unitIds))
            ->when($gender, fn ($q) => $q->where('jenis_kelamin', $gender))
            ->where(fn ($q) => $q
                ->whereDoesntHave('halaqoh')
                ->when($currentGuruId, fn ($sub) => $sub->orWhere('id', $currentGuruId))
            )
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    protected static function getAccessibleUnitIds($user): array
    {
        if (! $user) {
            return [];
        }

        $clusterNames = [
            'Pondok Pesantren As-Sunnah Gorontalo',
            'MTS As-Sunnah Gorontalo',
            'MA As-Sunnah Limboto Barat',
        ];

        $expandCluster = function (?int $unitId) use ($clusterNames): array {
            if (! $unitId) {
                return [];
            }

            $unit = Unit::find($unitId);
            if (! $unit) {
                return [$unitId];
            }

            $matchesCluster = collect($clusterNames)->contains(function ($name) use ($unit) {
                $current = strtolower($unit->nama_unit ?? '');
                $target = strtolower($name);

                return $current === $target || str_contains($current, $target);
            });

            if ($matchesCluster) {
                return Unit::whereIn('nama_unit', $clusterNames)->pluck('id')->all();
            }

            return [$unitId];
        };

        $unitIds = [];

        if ($user->unit_id) {
            $unitIds = array_merge($unitIds, $expandCluster($user->unit_id));
        }

        $jabatanUnitIds = $user->jabatans()
            ->pluck('guru_jabatan.unit_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($jabatanUnitIds as $jabatanUnitId) {
            $unitIds = array_merge($unitIds, $expandCluster($jabatanUnitId));
        }

        return array_values(array_unique($unitIds));
    }
}
