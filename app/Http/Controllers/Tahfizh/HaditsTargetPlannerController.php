<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\Hadits;
use App\Models\HaditsTarget;
use App\Support\TahfizhHadits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HaditsTargetPlannerController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(TahfizhHadits::userHasAccess($user), 403);

        $data = $request->validate([
            'halaqoh_id' => ['required', 'integer', 'exists:halaqoh,id'],
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'semester' => ['required', 'in:semester_1,semester_2'],
            'kitab' => ['required', 'string'],
            'hadits_start_id' => ['required', 'integer'],
            'hadits_end_id' => ['required', 'integer'],
        ]);

        // Default status target ke "berjalan" agar penyimpanan tidak gagal setelah field dihilangkan dari form.
        $data['status'] = 'berjalan';

        if (! TahfizhHadits::userCanManageSantri($user, (int) $data['santri_id'])) {
            return back()->withErrors(['santri_id' => 'Santri tidak berada dalam halaqoh atau unit yang Anda kelola.'])->withInput();
        }

        $belongsToHalaqoh = DB::table('halaqoh_santri')
            ->where('halaqoh_id', $data['halaqoh_id'])
            ->where('santri_id', $data['santri_id'])
            ->exists();

        if (! $belongsToHalaqoh) {
            return back()->withErrors(['halaqoh_id' => 'Santri tidak terdaftar pada halaqoh yang dipilih.'])->withInput();
        }

        $haditsList = Hadits::query()
            ->where('kitab', $data['kitab'])
            ->orderBy('nomor')
            ->get(['id', 'nomor', 'judul']);

        if ($haditsList->isEmpty()) {
            return back()->withErrors(['kitab' => 'Kitab hadits tidak ditemukan.'])->withInput();
        }

        $start = $haditsList->firstWhere('id', (int) $data['hadits_start_id']);
        $end = $haditsList->firstWhere('id', (int) $data['hadits_end_id']);

        if (! $start || ! $end) {
            return back()->withErrors(['hadits_start_id' => 'Hadits awal/akhir tidak valid.'])->withInput();
        }

        if (($start->nomor ?? 0) > ($end->nomor ?? 0)) {
            return back()->withErrors(['hadits_start_id' => 'Hadits awal harus lebih kecil daripada hadits akhir.'])->withInput();
        }

        $rangeIds = $haditsList
            ->filter(fn ($item) => ($item->nomor ?? 0) >= ($start->nomor ?? 0) && ($item->nomor ?? 0) <= ($end->nomor ?? 0))
            ->pluck('id')
            ->values();

        if ($rangeIds->isEmpty()) {
            return back()->withErrors(['hadits_start_id' => 'Rentang hadits tidak valid.'])->withInput();
        }

        $created = 0;

        DB::transaction(function () use ($rangeIds, $data, &$created) {
            foreach ($rangeIds as $haditsId) {
                HaditsTarget::updateOrCreate(
                    [
                        'santri_id' => $data['santri_id'],
                        'hadits_id' => $haditsId,
                        'tahun' => $data['tahun'],
                        'semester' => $data['semester'],
                    ],
                    [
                        'status' => $data['status'],
                    ]
                );
                $created++;
            }
        });

        return back()->with('success', "{$created} target hadits berhasil diperbarui untuk santri terpilih.");
    }
}
