<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SantriResource\Pages;
use App\Models\Santri;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SantriResource extends Resource
{
    protected static ?string $model = Santri::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Santri';
    protected static ?string $navigationGroup = 'Data Pokok';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Santri')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('nisy')
                            ->label('NISY')
                            ->unique(Santri::class, 'nisy', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nisn')
                            ->label('NISN')
                            ->unique(Santri::class, 'nisn', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Toggle::make('status_aktif')
                            ->label('Status Aktif')
                            ->default(true),

                        Forms\Components\FileUpload::make('foto')
                            ->label('Foto')
                            ->directory('santri/photos')
                            ->image()
                            ->maxSize(2048),
                    ])->columns(2),

                Forms\Components\Section::make('Unit & Akademik')
                    ->schema([
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit Pendidikan')
                            ->relationship('unit', 'nama_unit')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('tahun_masuk')
                            ->label('Tahun Masuk')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(now()->year),
                    ])->columns(2),

                Forms\Components\Section::make('Wali Santri')
                    ->schema([
                        Forms\Components\TextInput::make('nama_wali')
                            ->label('Nama Wali')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('no_hp_wali')
                            ->label('No. HP Wali')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('username_wali')
                            ->label('Username Wali (Auto)')
                            ->content(fn ($record) => $record?->generateWaliUsername() ?? 'Akan digenerate saat simpan'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nisy')
                    ->label('NISY')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('jenis_kelamin')
                    ->label('JK')
                    ->colors([
                        'primary' => 'L',
                        'danger' => 'P',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'L' ? 'Laki' : 'Perempuan'),

                Tables\Columns\TextColumn::make('unit.nama_unit')
                    ->label('Unit')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('tahun_masuk')
                    ->label('Tahun Masuk')
                    ->sortable(),

                Tables\Columns\TextColumn::make('wali_username')
                    ->label('Username')
                    ->state(fn (Santri $record) => $record->wali->first()?->user?->username
                        ?? $record->wali->first()?->username_wali
                        ?? '-')
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'nama_unit'),

                Tables\Filters\TernaryFilter::make('status_aktif')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole([
                        'superadmin',
                        'admin',
                        'admin_unit',
                        'kepala_madrasah',
                        'wakamad_kurikulum',
                        'wakamad_kesiswaan',
                        'wakamad_sarpras',
                        'bendahara',
                    ])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['superadmin'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nama');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['wali.user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSantris::route('/'),
        ];
    }
}
