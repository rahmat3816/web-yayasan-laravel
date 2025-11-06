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

            return match ($role) {
                'superadmin', 'admin', 'operator' => redirect()->route('admin.dashboard'),
                'guru', 'koordinator_tahfizh_putra', 'koordinator_tahfizh_putri' => redirect()->route('guru.dashboard'),
                'wali_santri' => redirect()->route('wali.dashboard'),
                'pimpinan' => redirect()->route('pimpinan.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}
