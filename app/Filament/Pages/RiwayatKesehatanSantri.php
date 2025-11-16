<?php

namespace App\Filament\Pages;

use App\Models\SantriHealthLog;
use App\Support\KesehatanScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Santri;

class RiwayatKesehatanSantri extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Riwayat Kesehatan';

    protected static ?string $navigationGroup = 'Kesehatan';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'kesehatan/riwayat';

    protected static string $view = 'filament.pages.kesehatan-riwayat';

    public $filterSantriId = null;
    public $filterYear = null;
    public $filterMonth = null;
    public $filterAsramaId = null;
    public array $santriOptions = [];
    public array $yearOptions = [];
    public array $monthOptions = [];
    public array $asramaOptions = [];
    public bool $asramaLocked = false;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasKesehatanFullAccess() || $user->isActiveMusyrif() || $user->hasKesantrianManagementAccess());
    }

    public function getHeading(): string
    {
        return 'Riwayat Kesehatan Santri';
    }

    public function mount(): void
    {
        $this->loadFilterOptions();
    }

    protected function loadFilterOptions(): void
    {
        $user = auth()->user();
        $years = range(now()->year, now()->year - 5);
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->setMonth($m)->translatedFormat('F')]);

        $santriQuery = Santri::query()->orderBy('nama');
        KesehatanScope::applyUnitFilter($santriQuery, 'self');
        if ($gender = $user?->kesehatanGenderScope()) {
            $santriQuery->where('jenis_kelamin', $gender);
        }

        $asramaQuery = \App\Models\Asrama::query()->orderBy('nama');
        $musyrifAsramaIds = [];
        if ($user?->isActiveMusyrif()) {
            $musyrifAsramaIds = $user->activeMusyrifAssignments()->pluck('asrama_id')->all();
            if (! empty($musyrifAsramaIds)) {
                $asramaQuery->whereIn('id', $musyrifAsramaIds);
                $santriQuery->whereIn('asrama_id', $musyrifAsramaIds);
                $this->asramaLocked = true;
                $this->filterAsramaId = $this->filterAsramaId ?? ($musyrifAsramaIds[0] ?? null);
            }
        }

        $this->santriOptions = $santriQuery->pluck('nama', 'id')->toArray();
        $this->yearOptions = collect($years)->values()->toArray();
        $this->monthOptions = $months->toArray();
        $this->asramaOptions = $asramaQuery->pluck('nama', 'id')->toArray();
    }

    public function updatedFilterSantriId($value): void
    {
        $this->filterSantriId = $value ?: null;
        $this->resetTable();
    }

    public function updatedFilterYear($value): void
    {
        $this->filterYear = $value !== '' ? (int) $value : null;
        $this->resetTable();
    }

    public function updatedFilterMonth($value): void
    {
        $this->filterMonth = $value !== '' ? (int) $value : null;
        $this->resetTable();
    }

    public function updatedFilterAsramaId($value): void
    {
        $this->filterAsramaId = $value ?: null;
        $this->resetTable();
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        $query = SantriHealthLog::query()->with(['santri.unit', 'asrama', 'keluhanRef', 'penangananRef']);

        KesehatanScope::applyUnitFilter($query);
        KesehatanScope::applyGenderFilter($query, $user?->kesehatanGenderScope());

        if (! $user?->hasKesehatanFullAccess() && $user?->isActiveMusyrif()) {
            $guruId = $user->linked_guru_id ?? $user->ensureLinkedGuruId($user->name);
            if ($guruId) {
                $query->where('reporter_id', $guruId);
            }
        }

        if ($this->filterSantriId) {
            $query->where('santri_id', $this->filterSantriId);
        }
        if ($this->filterAsramaId) {
            $query->where('asrama_id', $this->filterAsramaId);
        }
        if ($this->filterYear) {
            $query->whereYear('tanggal_sakit', $this->filterYear);
        }
        if ($this->filterMonth) {
            $query->whereMonth('tanggal_sakit', $this->filterMonth);
        }

        return $query->latest('tanggal_sakit');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('tanggal_sakit')->label('Tanggal')->date(),
            Tables\Columns\TextColumn::make('asrama.nama')->label('Asrama')->badge(),
            Tables\Columns\TextColumn::make('keluhan')->label('Keluhan')->limit(25)->tooltip(fn ($record) => $record->keluhan),
            Tables\Columns\TextColumn::make('penanganan_sementara')
                ->label('Penanganan (Koor)')
                ->getStateUsing(function ($record) {
                    return $record->penanganan_sementara
                        ?: ($record->penangananRef->nama ?? ($record->status ? ucfirst($record->status) : 'Belum diisi'));
                })
                ->limit(25)
                ->tooltip(fn ($record) => $record->penanganan_sementara ?: ($record->penangananRef->nama ?? ($record->status ? ucfirst($record->status) : 'Belum diisi'))),
            Tables\Columns\TextColumn::make('tindak_lanjut_kesantrian')
                ->label('Tindak Lanjut Kesantrian')
                ->getStateUsing(fn ($record) => $record->penangananRef->nama ?? ($record->penanganan_sementara ?: ucfirst($record->status ?? 'Belum diisi')))
                ->limit(25)
                ->tooltip(fn ($record) => $record->penangananRef->nama ?? ($record->penanganan_sementara ?: ucfirst($record->status ?? 'Belum diisi'))),
        ];
    }

    protected function getTableFilters(): array
    {
        return [];
    }
}
