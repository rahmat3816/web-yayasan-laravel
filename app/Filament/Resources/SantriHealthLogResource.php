<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SantriHealthLogResource\Pages;
use App\Filament\Resources\SantriHealthLogResource\RelationManagers\ActionsRelationManager;
use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use App\Models\KeluhanSakit;
use App\Models\PenangananSementara;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SantriHealthLogResource extends Resource
{
    protected static ?string $model = SantriHealthLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Kesehatan';

    protected static ?string $navigationLabel = 'Log Kesehatan';

    protected static ?int $navigationSort = 2;

    protected static array $rujukSlugs = ['rujuk-klinik', 'rujuk-puskesmas', 'rujuk-rumah-sakit'];
    protected static array $kesantrianFollowupOptions = [
        'selesai' => 'Selesai ditangani',
        'rujuk-puskesmas' => 'Rujuk ke puskesmas',
        'rujuk-klinik' => 'Rujuk ke klinik',
        'rujuk-rumah-sakit' => 'Rujuk ke rumah sakit',
    ];

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasKesehatanFullAccess() || $user->isActiveMusyrif());
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $musyrifAsramaIds = $user?->activeMusyrifAssignments()->pluck('asrama_id')->all() ?? [];
        $filterGender = $user?->kesehatanGenderScope();
        $isKesehatan = $user?->hasKesehatanFullAccess();
        return $form
            ->schema([
                Forms\Components\Select::make('asrama_id')
                    ->label('Asrama')
                    ->relationship('asrama', 'nama', function (Builder $query) use ($musyrifAsramaIds) {
                        if (!empty($musyrifAsramaIds)) {
                            $query->whereIn('id', $musyrifAsramaIds);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->default(fn () => $musyrifAsramaIds[0] ?? null)
                    ->disabled(function ($component) use ($musyrifAsramaIds) {
                        $operation = method_exists($component->getLivewire(), 'getOperation')
                            ? $component->getLivewire()->getOperation()
                            : null;

                        return $operation === 'edit' || !empty($musyrifAsramaIds);
                    })
                    ->dehydrated(true)
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('santri_id')
                    ->label('Santri')
                    ->relationship('santri', 'nama', function (Builder $query, Forms\Get $get) use ($user, $musyrifAsramaIds, $filterGender) {
                        if ($user && !$user->hasKesehatanFullAccess() && !empty($musyrifAsramaIds)) {
                            $query->whereIn('asrama_id', $musyrifAsramaIds);
                        }
                        if ($filterGender) {
                            $query->where('jenis_kelamin', $filterGender);
                        }
                        if ($selectedAsrama = $get('asrama_id')) {
                            $query->where('asrama_id', $selectedAsrama);
                        }
                        KesehatanScope::applyUnitFilter($query, 'self');
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->disabled(function ($component) {
                        $operation = method_exists($component->getLivewire(), 'getOperation')
                            ? $component->getLivewire()->getOperation()
                            : null;

                        return $operation === 'edit';
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $santri = \App\Models\Santri::find($state);

                        if ($santri) {
                            $set('asrama_id', $santri->asrama_id);
                        }
                    }),
                Forms\Components\DatePicker::make('tanggal_sakit')
                    ->label('Tanggal Sakit')
                    ->default(now())
                    ->required()
                    ->disabled(function ($component) {
                        $operation = method_exists($component->getLivewire(), 'getOperation')
                            ? $component->getLivewire()->getOperation()
                            : null;

                        return $operation === 'edit';
                    }),
                Forms\Components\Select::make('keluhan_id')
                    ->label('Keluhan')
                    ->options(fn () => KeluhanSakit::orderBy('urutan')->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->disabled(function ($component) {
                        $operation = method_exists($component->getLivewire(), 'getOperation')
                            ? $component->getLivewire()->getOperation()
                            : null;

                        return $operation === 'edit';
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $keluhanText = $state ? (KeluhanSakit::find($state)?->nama ?? '') : '';
                        if ($get('keluhan_lain')) {
                            $keluhanText = $get('keluhan_lain');
                        }
                        $set('keluhan', $keluhanText);
                    }),
                Forms\Components\TextInput::make('keluhan_lain')
                    ->label('Keluhan Lainnya')
                    ->placeholder('Isi jika keluhan tidak ada di daftar')
                    ->visible(fn (callable $get) => optional(KeluhanSakit::find($get('keluhan_id')))->slug === 'lainnya')
                    ->reactive()
                    ->disabled(function ($component) {
                        $operation = method_exists($component->getLivewire(), 'getOperation')
                            ? $component->getLivewire()->getOperation()
                            : null;

                        return $operation === 'edit';
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('keluhan', $state ?: (KeluhanSakit::find($get('keluhan_id'))?->nama ?? ''));
                    }),
                Forms\Components\Hidden::make('keluhan')
                    ->default(fn (callable $get) => $get('keluhan_lain') ?: (KeluhanSakit::find($get('keluhan_id'))?->nama ?? ''))
                    ->required(),
                Forms\Components\Select::make('penanganan_id')
                    ->label('Penanganan Sementara')
                    ->options(fn () => PenangananSementara::orderBy('urutan')->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(fn () => auth()->user()?->hasKesehatanFullAccess())
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $penanganan = $state ? PenangananSementara::find($state) : null;
                        $nama = $penanganan?->nama ?? '';
                        $set('penanganan_sementara', $nama);

                        if ($penanganan && in_array($penanganan->slug, self::$rujukSlugs, true)) {
                            $set('status', 'dirujuk');
                        } elseif ($penanganan) {
                            $set('status', 'ditangani');
                        }
                    }),
                Forms\Components\TextInput::make('penanganan_sementara')
                    ->label('Catatan Penanganan')
                    ->placeholder('Tambahkan catatan singkat jika perlu')
                    ->maxLength(150),
                Forms\Components\Toggle::make('perlu_rujukan')
                    ->label('Perlu Rujukan Lanjut?'),
                Forms\Components\Hidden::make('reporter_id')
                    ->default(fn () => auth()->user()?->linked_guru_id ?? auth()->user()?->ensureLinkedGuruId(auth()->user()?->name)),
                Forms\Components\Hidden::make('musyrif_assignment_id')
                    ->default(fn () => auth()->user()?->activeMusyrifAssignments()->first()?->id),
                Forms\Components\Hidden::make('status')
                    ->default('menunggu'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('santri.nama')
                    ->searchable()
                    ->label('Santri'),
                Tables\Columns\TextColumn::make('asrama.nama')
                    ->label('Asrama')
                    ->badge(),
                Tables\Columns\TextColumn::make('tanggal_sakit')
                    ->date(),
                Tables\Columns\TextColumn::make('keluhan')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->keluhan),
                Tables\Columns\TextColumn::make('penanganan_sementara')
                    ->label('Penanganan (Koor)')
                    ->state(fn ($record) => $record->penanganan_sementara ?: ($record->penangananRef->nama ?? '-'))
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->penanganan_sementara ?: ($record->penangananRef->nama ?? '-')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'menunggu',
                        'info' => 'ditangani',
                        'danger' => 'dirujuk',
                        'success' => 'selesai',
                    ]),
                Tables\Columns\BooleanColumn::make('perlu_rujukan')
                    ->label('Rujukan?'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'ditangani' => 'Ditangani',
                        'dirujuk' => 'Dirujuk',
                        'selesai' => 'Selesai',
                    ]),
                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(collect(range(now()->year, now()->year - 5))->mapWithKeys(fn ($y) => [$y => $y]))
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value) {
                            $query->whereYear('tanggal_sakit', $value);
                        }
                    }),
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->setMonth($m)->translatedFormat('F')]))
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value) {
                            $query->whereMonth('tanggal_sakit', $value);
                        }
                    }),
                Tables\Filters\Filter::make('today')
                    ->label('Hanya Hari Ini')
                    ->query(fn ($query) => $query->whereDate('tanggal_sakit', today())),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(fn () => false),
                Action::make('tindak_lanjut')
                    ->label('Tindak Lanjut')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn () => auth()->user()?->hasRole([
                        'koor_kesehatan_putra',
                        'koor_kesehatan_putri',
                        'koordinator_kesehatan_putra',
                        'koordinator_kesehatan_putri',
                        'superadmin',
                    ]))
                    ->form(fn (SantriHealthLog $record) => [
                        Forms\Components\Placeholder::make('asrama_info')
                            ->label('Asrama')
                            ->content($record->asrama?->nama ?? '-'),
                        Forms\Components\Placeholder::make('santri_info')
                            ->label('Santri')
                            ->content($record->santri?->nama ?? '-'),
                        Forms\Components\Placeholder::make('tanggal_info')
                            ->label('Tanggal Sakit')
                            ->content(optional($record->tanggal_sakit)->format('d/m/Y')),
                        Forms\Components\Placeholder::make('keluhan_info')
                            ->label('Keluhan')
                            ->content($record->keluhan ?? '-'),
                        Forms\Components\Select::make('penanganan_id')
                            ->label('Penanganan Sementara')
                            ->options(fn () => PenangananSementara::orderBy('urutan')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('penanganan_sementara')
                            ->label('Catatan Penanganan')
                            ->placeholder('Tambahkan catatan singkat jika perlu')
                            ->maxLength(150),
                        Forms\Components\Toggle::make('perlu_rujukan')
                            ->label('Perlu Rujukan Lanjut?'),
                    ])
                    ->action(function (SantriHealthLog $record, array $data) {
                        $record->penanganan_id = $data['penanganan_id'];
                        $record->penanganan_sementara = $data['penanganan_sementara'] ?? null;
                        $record->perlu_rujukan = (bool) ($data['perlu_rujukan'] ?? false);

                        $penanganan = PenangananSementara::find($data['penanganan_id']);
                        if ($penanganan && in_array($penanganan->slug, self::$rujukSlugs, true)) {
                            $record->status = 'dirujuk';
                        } elseif ($penanganan) {
                            $record->status = 'ditangani';
                        }

                        $record->save();
                    })
                    ->modalWidth('md'),
                Action::make('tindak_lanjut_kesantrian')
                    ->label('Tindak Lanjut Kesantrian')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (SantriHealthLog $record) => auth()->user()?->hasKesantrianManagementAccess())
                    ->hidden(fn (SantriHealthLog $record) => $record->status !== 'ditangani')
                    ->modalHeading('Tindak Lanjut Kesantrian')
                    ->form(fn (SantriHealthLog $record) => [
                        Forms\Components\Placeholder::make('asrama_info_kes')
                            ->label('Asrama')
                            ->content($record->asrama?->nama ?? '-'),
                        Forms\Components\Placeholder::make('santri_info_kes')
                            ->label('Santri')
                            ->content($record->santri?->nama ?? '-'),
                        Forms\Components\Placeholder::make('keluhan_info_kes')
                            ->label('Keluhan')
                            ->content($record->keluhan ?? '-'),
                        Forms\Components\Placeholder::make('penanganan_info_kes')
                            ->label('Penanganan Sementara (Koor)')
                            ->content($record->penanganan_sementara ?: ($record->penangananRef->nama ?? '-')),
                        Forms\Components\Select::make('hasil')
                            ->label('Pilih tindak lanjut')
                            ->options(self::$kesantrianFollowupOptions)
                            ->required(),
                        Forms\Components\TextInput::make('catatan_kesantrian')
                            ->label('Catatan (opsional)')
                            ->maxLength(150),
                    ])
                    ->action(function (SantriHealthLog $record, array $data) {
                        $hasil = $data['hasil'] ?? null;
                        $catatan = $data['catatan_kesantrian'] ?? null;

                        $record->penanganan_sementara = $catatan ?: $record->penanganan_sementara;
                        $record->perlu_rujukan = in_array($hasil, ['rujuk-puskesmas', 'rujuk-klinik', 'rujuk-rumah-sakit'], true);

                        if ($record->perlu_rujukan) {
                            $record->status = 'dirujuk';
                            $slugToSet = $hasil;
                            $penanganan = PenangananSementara::where('slug', $slugToSet)->first();
                            if ($penanganan) {
                                $record->penanganan_id = $penanganan->id;
                                $record->penanganan_sementara = $penanganan->nama;
                            }
                        } elseif ($hasil === 'selesai') {
                            $record->status = 'selesai';
                        }

                        $record->save();
                    })
                    ->modalWidth('md'),
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

    public static function getRelations(): array
    {
        return [
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSantriHealthLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $filterGender = $user?->kesehatanGenderScope();

        if ($filterGender) {
            $query->whereHas('santri', fn ($q) => $q->where('jenis_kelamin', $filterGender));
        }

        KesehatanScope::applyUnitFilter($query);

        if (! $user) {
            return $query->whereNull('id');
        }

        if ($user->hasKesehatanFullAccess()) {
            return $query;
        }

        $guruId = $user->linked_guru_id ?? $user->ensureLinkedGuruId($user->name);

        if (! $guruId) {
            return $query->whereNull('id');
        }

        return $query->where('reporter_id', $guruId);
    }
}
