<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = strtolower($user->role ?? '');

            $adminRoles = [
                'superadmin',
                'admin',
                'admin_unit',
                'kepala_madrasah',
                'wakamad_kurikulum',
                'wakamad_kesiswaan',
                'wakamad_sarpras',
                'bendahara',
            ];

            if (in_array($role, $adminRoles, true)) {
                return redirect()->route('admin.dashboard');
            }

            if (in_array($role, ['guru', 'wali_kelas'], true)) {
                return redirect()->route('guru.dashboard');
            }

            if (in_array($role, ['koordinator_tahfizh_putra', 'koordinator_tahfizh_putri'], true)) {
                return redirect()->route('tahfizh.dashboard');
            }

            $pondokRoles = [
                'pimpinan',
                'mudir_pondok',
                'naibul_mudir',
                'naibatul_mudir',
                'kabag_kesantrian_putra',
                'kabag_kesantrian_putri',
                'kabag_umum',
                'koor_kesehatan_putra',
                'koor_kesehatan_putri',
                'koor_kebersihan_putra',
                'koor_kebersihan_putri',
                'koor_keamanan_putra',
                'koor_keamanan_putri',
                'koor_tahfizh_putra',
                'koor_tahfizh_putri',
                'koor_lughoh_putra',
                'koor_lughoh_putri',
                'koor_kepegawaian',
                'koor_sarpras',
                'koor_dapur',
                'koor_logistik',
            ];

            if (in_array($role, $pondokRoles, true)) {
                return redirect()->route('pimpinan.dashboard');
            }

            if ($role === 'wali_santri') {
                return redirect()->route('wali.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
