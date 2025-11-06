<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WaliDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $santriId = $user->linked_santri_id;

        $santri = DB::table('santri')->where('id', $santriId)->first();

        $totalHafalan = 0;
        if ($santri) {
            $totalHafalan = DB::table('hafalan_quran')
                ->where('santri_id', $santri->id)
                ->count();
        }

        return view('wali.dashboard', [
            'santri' => $santri,
            'totalHafalan' => $totalHafalan,
        ]);
    }
}
