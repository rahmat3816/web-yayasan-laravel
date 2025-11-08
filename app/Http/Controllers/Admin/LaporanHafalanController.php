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
            ->with(['santri:id,nama,unit_id', 'guru:id,nama', 'halaqoh:id,nama_halaqoh'])
            ->whereYear('tanggal_setor', $tahun)
            ->whereMonth('tanggal_setor', $bulan);

        if ($unitId) {
            $query->whereHas('santri', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
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

        // 🔹 Grafik per hari
        $grafikPerHari = $data->groupBy(fn($r) => Carbon::parse($r->tanggal_setor)->format('d M'))
            ->map->count();

        // 🔹 Grafik kumulatif ayat
        $totalAyatPerHari = [];
        $totalAyat = 0;

        foreach ($data->sortBy('tanggal_setor') as $h) {
            $tanggal = Carbon::parse($h->tanggal_setor)->format('d M');
            $jumlahAyat = max(0, ($h->ayah_end - $h->ayah_start + 1));
            $totalAyat += $jumlahAyat;
            $totalAyatPerHari[$tanggal] = $totalAyat;
        }

        // 🔹 Rekap per guru
        $rekapGuru = $data->groupBy('guru_id')->map(fn($g) => [
            'nama' => optional($g->first()->guru)->nama ?? '-',
            'jumlah_setoran' => $g->count(),
            'total_santri' => $g->unique('santri_id')->count(),
        ]);

        // 🔹 Grafik ayat kumulatif per santri
        $grafikSantri = $data->groupBy('santri_id')->mapWithKeys(function ($group, $santriId) {
            $nama = optional($group->first()->santri)->nama ?? "Santri ID $santriId";
            $totalAyat = $group->sum(function ($h) {
                return max(0, ($h->ayah_end - $h->ayah_start + 1));
            });
            return [$nama => $totalAyat];
        });

        // 🔹 Dropdown
        $units = Unit::orderBy('nama_unit')->get(['id', 'nama_unit']);
        $guruList = Guru::orderBy('nama')->get(['id', 'nama']);
        $santriList = Santri::orderBy('nama')->get(['id', 'nama']);

        return view('admin.laporan.hafalan', compact(
            'data', 'rekap', 'grafikPerHari',
            'rekapGuru', 'units', 'guruList', 'santriList',
            'tahun', 'bulan', 'unitId', 'guruId', 'santriId',
            'totalAyatPerHari', 'grafikSantri'
        ));
    }

    public function statistik(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        $unitId = $request->query('unit_id');
        $guruId = $request->query('guru_id');
        $santriId = $request->query('santri_id');

        $query = HafalanQuran::query()
            ->with(['santri:id,nama,unit_id', 'guru:id,nama']);

        if ($tahun) $query->whereYear('tanggal_setor', $tahun);
        if ($bulan) $query->whereMonth('tanggal_setor', $bulan);
        if ($unitId) $query->whereHas('santri', fn ($q) => $q->where('unit_id', $unitId));
        if ($guruId) $query->where('guru_id', $guruId);
        if ($santriId) $query->where('santri_id', $santriId);

        $data = $query->orderBy('tanggal_setor')->get();

        $rekap = [
            'total_setoran' => $data->count(),
            'total_santri'  => $data->unique('santri_id')->count(),
            'total_guru'    => $data->unique('guru_id')->count(),
            'total_halaman' => $data->sum(fn($h) => ($h->page_end && $h->page_start) ? ($h->page_end - $h->page_start + 1) : 0),
            'total_juz'     => $data->unique('juz_start')->count(),
            'total_surah'   => $data->unique('surah_id')->count(),
            'total_ayat'    => $data->sum(fn($h) => max(0, ($h->ayah_end - $h->ayah_start + 1))),
        ];

        $grafikSantri = [];
        if ($santriId) {
            $filtered = HafalanQuran::where('santri_id', $santriId)
                ->when($tahun, fn($q) => $q->whereYear('tanggal_setor', $tahun))
                ->when($bulan, fn($q) => $q->whereMonth('tanggal_setor', $bulan))
                ->orderBy('tanggal_setor')
                ->get();

            $total = 0;
            foreach ($filtered as $row) {
                $jumlahAyat = max(0, $row->ayah_end - $row->ayah_start + 1);
                $total += $jumlahAyat;
                $grafikSantri[] = [
                    'tanggal' => \Carbon\Carbon::parse($row->tanggal_setor)->format('Y-m-d'),
                    'total' => $total,
                ];
            }
        }

        $units = Unit::orderBy('nama_unit')->get(['id', 'nama_unit']);
        $guruList = Guru::orderBy('nama')->get(['id', 'nama']);
        $santriList = Santri::orderBy('nama')->get(['id', 'nama']);

        return view('admin.laporan.statistik', compact(
            'data', 'rekap', 'grafikSantri',
            'units', 'guruList', 'santriList',
            'tahun', 'bulan', 'unitId', 'guruId', 'santriId'
        ));
    }

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
