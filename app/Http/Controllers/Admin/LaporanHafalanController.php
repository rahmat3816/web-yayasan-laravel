<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\HafalanQuran;
use App\Models\Halaqoh;

class LaporanHafalanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int)($request->query('tahun', now()->year));
        $bulan = (int)($request->query('bulan', now()->month));
        $unitId = $request->query('unit_id');
        $guruId = $request->query('guru_id');
        $santriId = $request->query('santri_id');

        $query = HafalanQuran::query()
            ->with(['santri:id,nama,unit_id', 'guru:id,nama', 'halaqoh:id,nama'])
            ->whereYear('tanggal_setor', $tahun)
            ->whereMonth('tanggal_setor', $bulan);

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        if ($guruId) {
            $query->where('guru_id', $guruId);
        }
        if ($santriId) {
            $query->where('santri_id', $santriId);
        }

        $data = $query->orderByDesc('tanggal_setor')->get();

        // 🔹 Rekap total
        $rekap = [
            'total_setoran' => $data->count(),
            'total_santri'  => $data->unique('santri_id')->count(),
            'total_guru'    => $data->unique('guru_id')->count(),
            'total_halaman' => $data->sum(fn($h) => ($h->page_end && $h->page_start) ? ($h->page_end - $h->page_start + 1) : 0),
            'total_juz'     => $data->unique('juz_start')->count(),
            'total_surah'   => $data->unique('surah_id')->count(),
        ];

        // 🔹 Data untuk grafik capaian per hari
        $grafikPerHari = $data->groupBy(fn($r) => Carbon::parse($r->tanggal_setor)->format('d M'))
            ->map->count();

        // 🔹 Data untuk rekap per guru
        $rekapGuru = $data->groupBy('guru_id')->map(fn($g) => [
            'nama' => optional($g->first()->guru)->nama ?? '-',
            'jumlah_setoran' => $g->count(),
            'total_santri' => $g->unique('santri_id')->count(),
        ]);

        // 🔹 Dropdown filters
        $units = Unit::orderBy('nama_unit')->get(['id', 'nama_unit']);
        $guruList = Guru::orderBy('nama')->get(['id', 'nama']);
        $santriList = Santri::orderBy('nama')->get(['id', 'nama']);

        return view('admin.laporan.hafalan', compact(
            'data', 'rekap', 'grafikPerHari',
            'rekapGuru', 'units', 'guruList', 'santriList',
            'tahun', 'bulan', 'unitId', 'guruId', 'santriId'
        ));
    }

    // ===============================
    // 📈 Grafik Progres Hafalan per Santri (AJAX)
    // ===============================
    public function grafikSantri(int $id)
    {
        $data = HafalanQuran::where('santri_id', $id)
            ->orderBy('tanggal_setor')
            ->get(['tanggal_setor', 'juz_start', 'surah_id', 'ayah_start', 'ayah_end']);

        $grafik = [];
        $total = 0;

        foreach ($data as $h) {
            $jumlahAyat = max(0, ($h->ayah_end - $h->ayah_start) + 1);
            $total += $jumlahAyat;
            $grafik[] = [
                'tanggal' => \Carbon\Carbon::parse($h->tanggal_setor)->format('Y-m-d'),
                'total'   => $total,
            ];
        }

        return response()->json($grafik);
    }

}
