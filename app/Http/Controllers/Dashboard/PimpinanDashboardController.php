<?php
// ==============================
// 📘 Tahap 10.1 – Setup Controller Dashboard
// Tujuan: Membuat semua controller dashboard per role
// ==============================

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// ==============================
// PimpinanDashboardController
// ==============================
class PimpinanDashboardController extends Controller
{
    public function index()
    {
        $totalSantri = DB::table('santri')->count();
        $totalGuru = DB::table('guru')->count();
        $totalUnit = DB::table('units')->count();

        return view('pimpinan.dashboard', compact('totalSantri', 'totalGuru', 'totalUnit'));
    }
}
