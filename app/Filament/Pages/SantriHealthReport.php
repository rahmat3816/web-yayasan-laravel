<?php

namespace App\Filament\Pages;

use App\Models\SantriHealthLog;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class SantriHealthReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Kesehatan';

    protected static ?string $navigationLabel = 'Rekap Kesehatan';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'kesehatan/rekap';

    protected static string $view = 'filament.pages.santri-health-report';

    protected static array $allowedRoles = [
        'superadmin',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koordinator_kesehatan_putra',
        'koordinator_kesehatan_putri',
        'koor_kesehatan_putra',
        'koor_kesehatan_putri',
    ];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole(self::$allowedRoles) ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $query = SantriHealthLog::query()->with(['santri', 'asrama']);

        $user = auth()->user();

        if (! $user?->hasRole(self::$allowedRoles)) {
            $guruId = $user?->linked_guru_id ?? $user?->ensureLinkedGuruId($user?->name);
            $query->where('reporter_id', $guruId);
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('tanggal_sakit')->date(),
            Tables\Columns\TextColumn::make('santri.nama')->label('Santri')->searchable(),
            Tables\Columns\TextColumn::make('santri.unit.nama_unit')->label('Unit')->toggleable(),
            Tables\Columns\TextColumn::make('asrama.nama')->badge()->toggleable(),
            Tables\Columns\BadgeColumn::make('tingkat')
                ->colors([
                    'info' => 'sedang',
                    'warning' => 'berat',
                    'success' => 'ringan',
                ]),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'menunggu',
                    'info' => 'ditangani',
                    'danger' => 'dirujuk',
                    'success' => 'selesai',
                ]),
            Tables\Columns\BooleanColumn::make('perlu_rujukan')->label('Rujukan?'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('tanggal')
                ->form([
                    Forms\Components\DatePicker::make('from')->label('Dari'),
                    Forms\Components\DatePicker::make('until')->label('Sampai'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('tanggal_sakit', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('tanggal_sakit', '<=', $date));
                }),
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'menunggu' => 'Menunggu',
                    'ditangani' => 'Ditangani',
                    'dirujuk' => 'Dirujuk',
                    'selesai' => 'Selesai',
                ]),
            Tables\Filters\SelectFilter::make('tingkat')
                ->options([
                    'ringan' => 'Ringan',
                    'sedang' => 'Sedang',
                    'berat' => 'Berat',
                ]),
            Tables\Filters\SelectFilter::make('asrama_id')
                ->label('Asrama')
                ->relationship('asrama', 'nama')
                ->searchable()
                ->preload(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv()
    {
        $fileName = 'rekap-kesehatan-' . now()->format('Ymd_His') . '.csv';
        $query = $this->getFilteredTableQuery()->clone()->orderBy('tanggal_sakit');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Santri', 'Asrama', 'Unit', 'Keluhan', 'Tingkat', 'Status']);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        optional($log->tanggal_sakit)->format('Y-m-d'),
                        $log->santri->nama ?? '-',
                        $log->asrama->nama ?? '-',
                        $log->santri->unit->nama_unit ?? '-',
                        $log->keluhan,
                        ucfirst($log->tingkat),
                        ucfirst($log->status),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName);
    }
}
