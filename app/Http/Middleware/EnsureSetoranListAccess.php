<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSetoranListAccess
{
    /**
     * Memastikan hanya role tertentu yang dapat mengakses daftar setoran.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 🟢 Superadmin boleh lihat semua
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // 🟢 Guru & koordinator tahfizh boleh lanjut
        if (in_array($user->role, [
            'guru',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
        ])) {
            return $next($request);
        }

        // 🔴 Selain itu: redirect lembut ke dashboard
        return redirect()
            ->route('dashboard')
            ->with('error', 'Akses ke daftar setoran tidak diizinkan.');
    }
}
