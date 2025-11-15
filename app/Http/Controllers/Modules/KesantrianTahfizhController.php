<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Halaqoh;
use App\Models\Santri;

class KesantrianTahfizhController extends Controller
{
    public function show(string $segment)
    {
        $segment = strtolower($segment);
        abort_unless(in_array($segment, ['putra', 'putri']), 404);

        $genderCode = $segment === 'putra' ? 'L' : 'P';
        $genderLabel = $genderCode === 'L' ? 'Laki-laki' : 'Perempuan';
        $title = 'Tahfizh ' . ucfirst($segment);

        $halaqoh = Halaqoh::with([
                'guru:id,nama,jenis_kelamin,unit_id',
                'unit:id,nama_unit',
                'santri:id,nama,jenis_kelamin',
            ])
            ->whereHas('guru', fn($q) => $q->where('jenis_kelamin', $genderCode))
            ->orderBy('nama_halaqoh')
            ->get();

        $guru = Guru::with('unit:id,nama_unit')
            ->where('jenis_kelamin', $genderCode)
            ->orderBy('nama')
            ->get();

        $santriTotal = Santri::where('jenis_kelamin', $genderCode)->count();
        $santri = Santri::with('unit:id,nama_unit')
            ->where('jenis_kelamin', $genderCode)
            ->orderBy('nama')
            ->limit(100)
            ->get();

        $stats = [
            'halaqoh' => $halaqoh->count(),
            'guru' => $guru->count(),
            'santri' => $santriTotal,
        ];

        return view('modules.kesantrian.tahfizh', [
            'segment' => $segment,
            'genderCode' => $genderCode,
            'genderLabel' => $genderLabel,
            'title' => $title,
            'halaqoh' => $halaqoh,
            'guru' => $guru,
            'santri' => $santri,
            'stats' => $stats,
            'santriTotal' => $santriTotal,
        ]);
    }
}
