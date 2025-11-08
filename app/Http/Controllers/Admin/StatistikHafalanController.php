<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\HafalanQuran;

class StatistikHafalanController extends Controller
{
    public function index(Request $request)
    {
        $unitId   = $request->query('unit_id');
        $guruId   = $request->query('guru_id');
        $santriId = $request->query('santri_id');
        $tahun    = $request->query('tahun');
        $bulan    = $request->query('bulan');

        $query = HafalanQuran::query()->with(['santri:id,nama,unit_id', 'guru:id,nama']);

        if ($unitId) {
            $query->whereHas('santri', fn($q) => $q->where('unit_id', $unitId));
        }

        if ($guruId) {
            $query->where('guru_id', $guruId);
        }

        if ($santriId) {
            $query->where('santri_id', $santriId);
        }

        if ($tahun) {
            $query->whereYear('tanggal_setor', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_setor', $bulan);
        }

        $data = $query->orderBy('tanggal_setor')->get();

        $rekap = [
            'total_setoran' => $data->count(),
            'total_halaman' => $data->sum(fn($h) => ($h->page_end && $h->page_start) ? ($h->page_end - $h->page_start + 1) : 0),
            'total_ayat'    => $data->sum(fn($h) => max(0, ($h->ayah_end - $h->ayah_start + 1))),
        ];

        // Grafik kumulatif ayat untuk satu santri saja
        $grafikSantri = [];
        if ($santriId) {
            $totalAyat = 0;
            foreach ($data as $h) {
                $totalAyat += max(0, ($h->ayah_end - $h->ayah_start + 1));
                $grafikSantri[] = [
                    'tanggal' => Carbon::parse($h->tanggal_setor)->format('d M Y'),
                    'total'   => $totalAyat,
                ];
            }
        }

        $units     = Unit::orderBy('nama_unit')->get(['id','nama_unit']);
        $guruList  = Guru::orderBy('nama')->get(['id','nama']);
        $santriList= Santri::orderBy('nama')->get(['id','nama']);

        return view('admin.statistik.hafalan', compact(
            'units','guruList','santriList',
            'unitId','guruId','santriId','tahun','bulan',
            'rekap','data','grafikSantri'
        ));
    }
}
