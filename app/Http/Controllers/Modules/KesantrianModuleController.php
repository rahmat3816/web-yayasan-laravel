<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\SantriHealthLog;
use Illuminate\View\View;

class KesantrianModuleController extends Controller
{
    public function putra(): View
    {
        return $this->renderModule('putra');
    }

    public function putri(): View
    {
        return $this->renderModule('putri');
    }

    protected function renderModule(string $segment): View
    {
        $gender = $segment === 'putri' ? 'P' : 'L';

        $query = SantriHealthLog::query()
            ->with(['santri.unit', 'asrama'])
            ->whereHas('santri', fn ($q) => $q->where('jenis_kelamin', $gender));

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->whereIn('status', ['menunggu', 'ditangani'])->count(),
            'dirujuk' => (clone $query)->where('status', 'dirujuk')->count(),
            'today' => (clone $query)->whereDate('tanggal_sakit', now()->toDateString())->count(),
        ];

        $recentLogs = (clone $query)
            ->latest('tanggal_sakit')
            ->take(5)
            ->get();

        return view("modules.kesantrian-{$segment}", [
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'segment' => $segment,
        ]);
    }
}
