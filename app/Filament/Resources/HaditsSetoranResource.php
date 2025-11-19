<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HaditsSetoranResource\Pages;
use App\Models\Hadits;
use App\Models\HaditsSetoran;
use App\Models\HaditsTarget;
use App\Support\TahfizhHadits;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HaditsSetoranResource extends Resource
{
    protected static ?string $model = HaditsSetoran::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Tahfizh Hadits';
    protected static ?string $navigationLabel = 'Setoran Hadits';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Setoran')
                    ->schema([
                        Forms\Components\Select::make('target_id')
                            ->label('Target Hadits')
                            ->options(function () {
                                $santriFilter = (int) request()->query('santri_id');

                                return HaditsTarget::with(['santri', 'hadits'])
                                    ->whereHas('santri', fn ($q) => $q->whereIn('unit_id', static::allowedUnitIds()))
                                    ->when($santriFilter, fn ($q) => $q->where('santri_id', $santriFilter))
                                    ->orderBy('santri_id')
                                    ->orderBy('hadits_id')
                                    ->get()
                                    ->mapWithKeys(fn ($target) => [
                                        $target->id => $target->santri->nama.' - '.$target->hadits->judul
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->reactive(),
                        Forms\Components\DatePicker::make('tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('penilai_id')
                            ->label('Penilai')
                            ->relationship('penilai', 'nama', fn ($query) => $query->whereIn('unit_id', static::allowedUnitIds()))
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()?->ensureLinkedGuruId(auth()->user()?->name))
                            ->required(),
                        Forms\Components\TextInput::make('nilai_tajwid')
                            ->numeric()->minValue(0)->maxValue(10),
                        Forms\Components\TextInput::make('nilai_mutqin')
                            ->numeric()->minValue(0)->maxValue(10),
                        Forms\Components\Textarea::make('catatan')->rows(3),
                    ])->columns(2),
                Section::make('Detail Segmen')
                    ->schema([
                        Repeater::make('details')
                            ->relationship('details')
                            ->schema([
                                Forms\Components\Select::make('segment_id')
                                    ->label('Segmen')
                                    ->options(function (callable $get) {
                                        $targetId = $get('../../target_id') ?? null;
                                        if (! $targetId) {
                                            return [];
                                        }
                                        $target = HaditsTarget::with('hadits.segments')->find($targetId);
                                        return optional($target?->hadits?->segments)->pluck('teks', 'id') ?? [];
                                    })
                                    ->required()
                                    ->reactive(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'belum' => 'Belum',
                                        'ulang' => 'Ulang',
                                        'lulus' => 'Lulus',
                                    ])
                                    ->default('lulus')
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('target.santri.nama')->label('Santri')->searchable(),
                TextColumn::make('target.hadits.judul')->label('Hadits')->wrap(),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('penilai.nama')->label('Penilai'),
            ])
            ->filters([
                SelectFilter::make('hadits_id')
                    ->label('Hadits')
                    ->options(fn () => Hadits::orderBy('kitab')->orderBy('urutan')->pluck('judul', 'id')->toArray())
                    ->query(fn ($query, $state) => $query->when($state, fn ($q, $haditsId) => $q->whereHas('target', fn ($targetQuery) => $targetQuery->where('hadits_id', $haditsId)))),
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
            'index' => Pages\ManageHaditsSetorans::route('/'),
        ];
    }

    protected static function allowedUnitIds(): array
    {
        return TahfizhHadits::unitIds();
    }
}
