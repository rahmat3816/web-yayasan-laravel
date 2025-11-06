<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user();
        $role = strtolower($me->role ?? '');

        // Deteksi superadmin pakai dua cara (kolom role & spatie role)
        $isSuperadmin = $role === 'superadmin' || (method_exists($me, 'hasRole') && $me->hasRole('superadmin'));

        if ($isSuperadmin) {
            // ==== Statistik global (superadmin) ====
            $stats = [
                'totalSantri'  => DB::table('santri')->count(),
                'totalGuru'    => DB::table('guru')->count(),
                'totalHalaqoh' => DB::table('halaqoh')->count(),
                'totalUnits'   => DB::table('units')->count(),
                'totalUsers'   => DB::table('users')->count(),
            ];

            // Pencarian user
            $q = trim((string) $request->get('q', ''));
            $users = User::query()
                ->with('roles') // dari Spatie\Permission\Traits\HasRoles
                ->when($q !== '', function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('username', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString();

            // Ambil daftar unit untuk mapping cepat (id => nama)
            $unitMap = DB::table('units')->pluck('nama_unit', 'id');

            return view('admin.dashboard', [
                'isSuperadmin' => true,
                'stats'        => $stats,
                'users'        => $users,
                'unitMap'      => $unitMap,
                'search'       => $q,
            ]);
        }

        // ==== Statistik per-unit (admin/operator) ====
        $unitId = (int) ($me->unit_id ?? 0);

        $stats = [
            'totalSantri'  => DB::table('santri')->when($unitId, fn($q) => $q->where('unit_id', $unitId))->count(),
            'totalGuru'    => DB::table('guru')->when($unitId, fn($q) => $q->where('unit_id', $unitId))->count(),
            'totalHalaqoh' => DB::table('halaqoh')->when($unitId, fn($q) => $q->where('unit_id', $unitId))->count(),
            // untuk admin/operator kita tidak tampilkan "totalUnits" & "totalUsers"
        ];

        return view('admin.dashboard', [
            'isSuperadmin' => false,
            'stats'        => $stats,
        ]);
    }
}
