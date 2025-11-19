<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HaditsResource\Pages;
use App\Models\Hadits;
use App\Support\TahfizhHadits;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HaditsResource extends Resource
{
    protected static ?string $model = Hadits::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Tahfizh Hadits';
    protected static ?string $navigationLabel = 'Master Hadits';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Hadits')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Hadits')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('kitab')
                            ->label('Kitab')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bab')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('urutan')
                            ->numeric(),
                        Forms\Components\Textarea::make('teks_arab')
                            ->rows(5),
                        Forms\Components\Textarea::make('teks_terjemah')
                            ->rows(5),
                    ])->columns(2),
                Section::make('Segmen Hadits')
                    ->schema([
                        Repeater::make('segments')
                            ->relationship('segments')
                            ->orderable('urutan')
                            ->schema([
                                Forms\Components\TextInput::make('urutan')
                                    ->numeric()
                                    ->label('Urutan')
                                    ->required(),
                                Forms\Components\Textarea::make('teks')
                                    ->label('Teks Segmen')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->minItems(1)
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('judul')->searchable()->sortable(),
                TextColumn::make('kitab')->toggleable(),
                TextColumn::make('urutan')->sortable(),
                TextColumn::make('segments_count')
                    ->counts('segments')
                    ->label('Segmen'),
            ])
            ->defaultSort('kitab')
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
            'index' => Pages\ManageHadits::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return TahfizhHadits::userHasAccess(auth()->user());
    }
}
