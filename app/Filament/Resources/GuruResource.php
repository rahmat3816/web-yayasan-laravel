<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuruResource\Pages;
use App\Models\Guru;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GuruResource extends Resource
{
    protected static ?string $model = Guru::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Guru';
    protected static ?string $navigationGroup = 'Data Pokok';
    protected static ?int $navigationSort = 2;

    protected const MANAGER_ROLES = [
        'superadmin',
        'admin',
        'admin_unit',
        'kepala_madrasah',
    ];

    protected static function canManage(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(self::MANAGER_ROLES)
            : false;
    }

    public static function canCreate(): bool
    {
        return self::canManage();
    }

    public static function canEdit(Model $record): bool
    {
        return self::canManage();
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole('superadmin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Guru')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('nipy')
                            ->label('NIPY')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, ?Guru $record) => $component->state($record?->nipy))
                            ->helperText('Dibentuk otomatis dari tanggal bergabung.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Penugasan & Kontak')
                    ->schema([
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit Pendidikan')
                            ->relationship('unit', 'nama_unit')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('Nomor HP')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->colors([
                        'warning' => 'L',
                        'danger' => 'P',
                    ])
                    ->formatStateUsing(fn (string $state) => $state === 'L' ? 'Laki-laki' : 'Perempuan'),

                Tables\Columns\TextColumn::make('nipy')
                    ->label('NIPY')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_bergabung')
                    ->label('Tgl Bergabung')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('Kontak')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),

                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => self::canManage()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('superadmin') ?? false),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGurus::route('/'),
            'create' => Pages\CreateGuru::route('/create'),
            'edit' => Pages\EditGuru::route('/{record}/edit'),
        ];
    }
}
