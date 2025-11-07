<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\Guru;
use App\Models\Unit;
use App\Models\Halaqoh;

class LaporanController extends Controller
{
    /**
     * 📊 Tampilkan halaman utama laporan
     */
    public function index()
    {
        $totalSantri  = Santri::count();
        $totalGuru    = Guru::count();
        $totalUnit    = Unit::count();
        $totalHalaqoh = Halaqoh::count();

        // Statistik Santri per Unit
        $santriPerUnit = Unit::from('units as u')
            ->leftJoin('santri as s', 'u.id', '=', 's.unit_id')
            ->select('u.nama_unit', \DB::raw('COUNT(s.id) as total'))
            ->groupBy('u.id', 'u.nama_unit')
            ->orderBy('u.nama_unit')
            ->get();

        return view('admin.laporan.index', compact(
            'totalSantri',
            'totalGuru',
            'totalUnit',
            'totalHalaqoh',
            'santriPerUnit'
        ));
    }
}
