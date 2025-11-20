<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\Mutun;
use App\Models\MutunTarget;
use App\Support\TahfizhMutun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MutunTargetPlannerController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        abort_unless(TahfizhMutun::userHasManagementAccess($user), 403);

        $data = $request->validate([
            'halaqoh_id' => ['required', 'integer', 'exists:halaqoh,id'],
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'semester' => ['required', 'in:semester_1,semester_2'],
            'kitab' => ['required', 'string'],
            'mutun_start_id' => ['required', 'integer'],
            'mutun_end_id' => ['required', 'integer'],
        ]);

        $belongsToHalaqoh = DB::table('halaqoh_santri')
            ->where('halaqoh_id', $data['halaqoh_id'])
            ->where('santri_id', $data['santri_id'])
            ->exists();

        if (! $belongsToHalaqoh) {
            return back()->withErrors(['halaqoh_id' => 'Santri tidak terdaftar pada halaqoh yang dipilih.'])->withInput();
        }

        $mutunList = Mutun::query()
            ->where('kitab', $data['kitab'])
            ->orderBy('nomor')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get(['id', 'judul', 'nomor', 'urutan']);

        if ($mutunList->isEmpty()) {
            return back()->withErrors(['kitab' => 'Kitab mutun tidak ditemukan.'])->withInput();
        }

        $start = $mutunList->firstWhere('id', (int) $data['mutun_start_id']);
        $end = $mutunList->firstWhere('id', (int) $data['mutun_end_id']);

        if (! $start || ! $end) {
            return back()->withErrors(['mutun_start_id' => 'Mutun awal/akhir tidak valid.'])->withInput();
        }

        $startOrder = $start->nomor ?? $start->urutan ?? 0;
        $endOrder = $end->nomor ?? $end->urutan ?? 0;

        if ($startOrder > $endOrder) {
            return back()->withErrors(['mutun_start_id' => 'Mutun awal harus lebih kecil daripada mutun akhir.'])->withInput();
        }

        $rangeIds = $mutunList
            ->filter(function ($item) use ($startOrder, $endOrder) {
                $order = $item->nomor ?? $item->urutan ?? 0;
                return $order >= $startOrder && $order <= $endOrder;
            })
            ->pluck('id')
            ->values();

        if ($rangeIds->isEmpty()) {
            return back()->withErrors(['mutun_start_id' => 'Rentang mutun tidak valid.'])->withInput();
        }

        $created = 0;

        DB::transaction(function () use ($rangeIds, $data, &$created) {
            foreach ($rangeIds as $mutunId) {
                MutunTarget::updateOrCreate(
                    [
                        'santri_id' => $data['santri_id'],
                        'mutun_id' => $mutunId,
                        'tahun' => $data['tahun'],
                        'semester' => $data['semester'],
                    ],
                    [
                        'status' => 'berjalan',
                    ]
                );
                $created++;
            }
        });

        return back()->with('success', "{$created} target mutun berhasil diperbarui untuk santri terpilih.");
    }
}
