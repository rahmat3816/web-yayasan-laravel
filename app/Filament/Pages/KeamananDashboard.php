<?php

namespace App\Filament\Pages;

use App\Models\PelanggaranLog;
use App\Models\PelanggaranSantriStat;
use App\Models\PelanggaranType;
use App\Support\KeamananAccess;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class KeamananDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Dashboard Keamanan';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'keamanan/dashboard';

    protected static string $view = 'filament.pages.keamanan-dashboard';

    public array $stats = [];

    public array $recentLogs = [];

    public function mount(): void
    {
        abort_unless(KeamananAccess::userHasManagementAccess(auth()->user()), 403);

        $this->loadStats();
        $this->loadRecentLogs();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return KeamananAccess::userHasManagementAccess(auth()->user());
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function loadStats(): void
    {
        $this->stats = [
            'jenis_pelanggaran' => PelanggaranType::count(),
            'total_log' => PelanggaranLog::count(),
            'sp1' => PelanggaranSantriStat::where('sp_level', 1)->count(),
            'sp2' => PelanggaranSantriStat::where('sp_level', 2)->count(),
            'sp3' => PelanggaranSantriStat::where('sp_level', 3)->count(),
        ];
    }

    protected function loadRecentLogs(): void
    {
        $this->recentLogs = PelanggaranLog::query()
            ->with(['santri:id,nama', 'type:id,nama', 'kategori:id,nama'])
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn ($log) => [
                'santri' => $log->santri?->nama ?? '-',
                'pelanggaran' => $log->type?->nama ?? '-',
                'kategori' => $log->kategori?->nama ?? '-',
                'poin' => $log->poin,
                'sp' => $log->sp_level,
                'tanggal' => optional($log->created_at)->format('d M Y'),
            ])
            ->all();
    }
}
