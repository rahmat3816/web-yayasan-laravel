<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MusyrifAssignmentResource\Pages;
use App\Models\MusyrifAssignment;
use App\Models\Unit;
use App\Models\Guru;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MusyrifAssignmentResource extends Resource
{
    protected static ?string $model = MusyrifAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Kesantrian';

    protected static ?string $navigationLabel = 'Penugasan Musyrif';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasKesantrianManagementAccess() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('guru_id')
                    ->label('Guru (Musyrif/Musyrifah)')
                    ->relationship('guru', 'nama', function ($query) {
                        $allowedUnitIds = static::allowedUnitIds();
                        $genderScope = static::genderScope();

                        $query->whereIn('unit_id', $allowedUnitIds);

                        if ($genderScope) {
                            $query->where('jenis_kelamin', $genderScope);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('asrama_id')
                    ->label('Asrama')
                    ->relationship('asrama', 'nama', fn ($query) => $query->orderBy('nama'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('mulai_tugas')
                    ->label('Mulai Tugas')
                    ->required(),
                Forms\Components\DatePicker::make('selesai_tugas')
                    ->label('Selesai Tugas')
                    ->minDate(fn (callable $get) => $get('mulai_tugas')),
                Forms\Components\Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'selesai' => 'Selesai',
                    ])
                    ->default('aktif')
                    ->required(),
                Forms\Components\TextInput::make('shift')
                    ->label('Shift / Jadwal')
                    ->maxLength(100),
                Forms\Components\Textarea::make('catatan')
                    ->rows(3),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guru.nama')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asrama.nama')
                    ->label('Asrama')
                    ->badge(),
                Tables\Columns\TextColumn::make('mulai_tugas')->date(),
                Tables\Columns\TextColumn::make('selesai_tugas')->date()->placeholder('-'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'secondary' => 'nonaktif',
                        'warning' => 'selesai',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMusyrifAssignments::route('/'),
        ];
    }

    protected static function allowedUnitIds(): array
    {
        return Unit::whereIn('nama_unit', [
            'Pondok Pesantren As-Sunnah Gorontalo',
            'MTS As-Sunnah Gorontalo',
            'MA As-Sunnah Limboto Barat',
        ])->pluck('id')->all();
    }

    protected static function genderScope(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $roles = collect($user->roles?->pluck('name')->toArray() ?? [])
            ->map(fn ($r) => strtolower($r));

        if ($roles->contains(fn ($r) => str_contains($r, 'putri'))) {
            return 'P';
        }

        if ($roles->contains(fn ($r) => str_contains($r, 'putra'))) {
            return 'L';
        }

        return null;
    }
}
