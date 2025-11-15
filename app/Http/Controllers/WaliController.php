<?php

namespace App\Http\Controllers;

use App\Models\HafalanQuran;
use App\Models\WaliSantri;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaliController extends Controller
{
    public function profil()
    {
        $anak = $this->anakCollection()->loadCount('hafalan');

        return view('wali.profil', [
            'user' => Auth::user(),
            'anak' => $anak,
        ]);
    }

    public function progres(Request $request)
    {
        $anak = $this->anakCollection();

        if ($anak->isEmpty()) {
            return view('wali.progres.index', [
                'santriList' => $anak,
                'hafalan' => collect(),
                'summary' => ['total' => 0, 'bulan' => null],
                'filters' => [],
                'statusBreakdown' => [],
            ]);
        }

        $santriIds = $anak->pluck('id');
        $selectedSantri = (int) $request->query('santri_id');
        if (!$santriIds->contains($selectedSantri)) {
            $selectedSantri = null;
        }

        $allowedStatus = ['lulus', 'ulang', 'proses'];
        $status = $request->filled('status') && in_array($request->input('status'), $allowedStatus, true)
            ? $request->input('status')
            : null;

        $bulan = $request->input('bulan');
        $bulanRange = null;
        if ($bulan) {
            try {
                $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
                $bulanRange = [$start, (clone $start)->endOfMonth()];
            } catch (\Throwable $th) {
                $bulanRange = null;
                $bulan = null;
            }
        }

        $baseQuery = HafalanQuran::query()->whereIn('santri_id', $santriIds);

        if ($selectedSantri) {
            $baseQuery->where('santri_id', $selectedSantri);
        }

        if ($status === 'proses') {
            $baseQuery->whereNull('status');
        } elseif ($status) {
            $baseQuery->where('status', $status);
        }

        if ($bulanRange) {
            $baseQuery->whereBetween('tanggal_setor', $bulanRange);
        }

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'bulan' => $bulan,
        ];

        $statusBreakdown = (clone $baseQuery)
            ->selectRaw('COALESCE(status, \'proses\') as status_label, COUNT(*) as total')
            ->groupBy('status_label')
            ->pluck('total', 'status_label')
            ->toArray();

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $hafalan = (clone $baseQuery)
            ->with(['santri:id,nama', 'guru:id,nama'])
            ->orderByDesc('tanggal_setor')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('wali.progres.index', [
            'santriList' => $anak,
            'hafalan' => $hafalan,
            'summary' => $summary,
            'filters' => [
                'santri_id' => $selectedSantri,
                'status' => $status,
                'bulan' => $bulan,
                'per_page' => $perPage,
            ],
            'statusBreakdown' => $statusBreakdown,
        ]);
    }

    public function hafalan()
    {
        $anak = $this->anakCollection();

        if ($anak->isEmpty()) {
            return view('wali.hafalan', [
                'anakOverview' => collect(),
                'monthlyTrend' => collect(),
                'recentSetoran' => collect(),
            ]);
        }

        $santriIds = $anak->pluck('id');
        $hafalanBase = HafalanQuran::query()->whereIn('santri_id', $santriIds);

        $perAnak = (clone $hafalanBase)
            ->select(
                'santri_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "lulus" THEN 1 ELSE 0 END) as total_lulus'),
                DB::raw('MAX(tanggal_setor) as terakhir_setor')
            )
            ->groupBy('santri_id')
            ->get()
            ->keyBy('santri_id');

        $anakOverview = $anak->map(function ($santri) use ($perAnak) {
            $stat = $perAnak->get($santri->id);
            return [
                'santri' => $santri,
                'total_setoran' => $stat->total ?? 0,
                'total_lulus' => $stat->total_lulus ?? 0,
                'terakhir_setor' => isset($stat->terakhir_setor)
                    ? Carbon::parse($stat->terakhir_setor)
                    : null,
            ];
        });

        $monthlyTrend = (clone $hafalanBase)
            ->selectRaw('DATE_FORMAT(tanggal_setor, "%Y-%m") as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                try {
                    $item->label = Carbon::createFromFormat('Y-m', $item->bulan)->translatedFormat('F Y');
                } catch (\Throwable $th) {
                    $item->label = $item->bulan;
                }
                return $item;
            });

        $recentSetoran = (clone $hafalanBase)
            ->with(['santri:id,nama'])
            ->latest('tanggal_setor')
            ->limit(10)
            ->get();

        return view('wali.hafalan', [
            'anakOverview' => $anakOverview,
            'monthlyTrend' => $monthlyTrend,
            'recentSetoran' => $recentSetoran,
        ]);
    }

    protected function anakCollection(): Collection
    {
        return WaliSantri::with('santri.unit')
            ->where('user_id', Auth::id())
            ->get()
            ->pluck('santri')
            ->filter()
            ->values();
    }
}
