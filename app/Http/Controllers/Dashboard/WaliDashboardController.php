<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HafalanQuran;
use App\Models\WaliSantri;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $anak = WaliSantri::with('santri.unit')
            ->where('user_id', $user->id)
            ->get()
            ->pluck('santri')
            ->filter();

        if ($anak->isEmpty()) {
            return view('wali.dashboard', [
                'rekap' => [
                    'total_anak' => 0,
                    'total_setoran' => 0,
                    'bulan_ini' => 0,
                ],
                'anakOverview' => collect(),
                'recentSetoran' => collect(),
            ]);
        }

        $santriIds = $anak->pluck('id');
        $hafalanBase = HafalanQuran::query()->whereIn('santri_id', $santriIds);

        $totalSetoran = (clone $hafalanBase)->count();
        $bulanIni = (clone $hafalanBase)
            ->whereBetween('tanggal_setor', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $perAnak = (clone $hafalanBase)
            ->select('santri_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(tanggal_setor) as terakhir_setor'))
            ->groupBy('santri_id')
            ->get()
            ->keyBy('santri_id');

        $recentSetoran = (clone $hafalanBase)
            ->with(['santri:id,nama', 'guru:id,nama'])
            ->latest('tanggal_setor')
            ->limit(5)
            ->get();

        $anakOverview = $anak->map(function ($santri) use ($perAnak) {
            $stat = $perAnak->get($santri->id);
            return [
                'santri' => $santri,
                'total_setoran' => $stat->total ?? 0,
                'terakhir_setor' => isset($stat->terakhir_setor)
                    ? Carbon::parse($stat->terakhir_setor)
                    : null,
            ];
        });

        return view('wali.dashboard', [
            'rekap' => [
                'total_anak' => $anak->count(),
                'total_setoran' => $totalSetoran,
                'bulan_ini' => $bulanIni,
            ],
            'anakOverview' => $anakOverview,
            'recentSetoran' => $recentSetoran,
        ]);
    }
}
