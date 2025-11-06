<?php
// ==============================
// 📘 Tahap 10.1 – Setup Controller Dashboard
// Tujuan: Membuat semua controller dashboard per role
// ==============================

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// ==============================
// TahfizhDashboardController
// ==============================
class TahfizhDashboardController extends Controller
{
    public function index()
    {
        $totalHalaqoh = DB::table('halaqoh')->count();
        $totalSantri = DB::table('halaqoh_santri')->count();
        $totalHafalan = DB::table('hafalan_quran')->count();

        return view('tahfizh.dashboard', compact('totalHalaqoh', 'totalSantri', 'totalHafalan'));
    }
}