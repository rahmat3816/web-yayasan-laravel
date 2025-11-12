<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HafalanQuran;
use App\Http\Controllers\Admin\LaporanHafalanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TahfizhQuranDashboard extends Component
{
    public ?int $unitId = null;
    public int $rangeDays = 7;
    public int $year;
    public int $month;
    public array $yearOptions = [];
    public array $monthOptions = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function mount(?int $unitId = null): void
    {
        $user = Auth::user();

        $this->year = now()->year;
        $this->month = now()->month;
        $this->yearOptions = collect(range(now()->year - 4, now()->year))
            ->reverse()
            ->values()
            ->all();

        if ($unitId) {
            $this->unitId = $unitId;
        } elseif ($user && !$user->hasRole('superadmin')) {
            $this->unitId = $user->unit_id;
        }
    }

    public function render()
    {
        $baseQuery = HafalanQuran::query()
            ->with(['santri:id,nama,unit_id', 'guru:id,nama'])
            ->whereYear('tanggal_setor', $this->year)
            ->whereMonth('tanggal_setor', $this->month)
            ->when($this->unitId, fn ($q) => $q->where('unit_id', $this->unitId));

        $dataset = $baseQuery->get();

        $stats = [
            'totalSetoran' => $dataset->count(),
            'setoranHariIni' => $dataset->where('tanggal_setor', Carbon::today()->toDateString())->count(),
            'santriAktif' => $dataset->unique('santri_id')->count(),
            'guruAktif' => $dataset->unique('guru_id')->count(),
        ];

        $trend = $dataset->groupBy(fn ($row) => Carbon::parse($row->tanggal_setor)->format('d M'))
            ->map->count()
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value])
            ->values();

        $topSantri = $dataset
            ->groupBy('santri_id')
            ->map(fn ($rows, $santriId) => [
                'santri_id' => $santriId,
                'nama' => optional($rows->first()->santri)->nama ?? 'Santri #' . $santriId,
                'total' => $rows->count(),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $rekap = LaporanHafalanController::hitungRekapHafalan($dataset);

        return view('livewire.tahfizh-quran-dashboard', [
            'stats' => $stats,
            'trend' => $trend,
            'topSantri' => $topSantri,
            'rekap' => $rekap,
            'unitName' => optional(optional(Auth::user())->unit)->nama_unit,
        ]);
    }
}
