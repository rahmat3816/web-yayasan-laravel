<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class RoleMiddleware
{
    /**
     * Format: middleware(['role:admin_unit|superadmin'])
     */
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        // Abaikan middleware saat testing
        if (app()->environment('testing')) {
            return $next($request);
        }       

        $user = Auth::user();

        // Cegah akses tanpa login
        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin akses penuh
        if (strtolower($user->role ?? '') === 'superadmin') {
            return $next($request);
        }

        // Jika middleware tanpa parameter → izinkan semua role login
        if (trim($roles) === '') {
            return $next($request);
        }

        // Validasi multi-role via daftar slug
        $allowedRoles = preg_split('/[|,]/', strtolower($roles));
        $userRole = strtolower($user->role ?? '');

        if (!in_array($userRole, $allowedRoles, true)) {
        // Untuk debugging bisa aktifkan log berikut:
        // \Log::warning('Role ditolak', ['role' => $userRole, 'allowed' => $allowedRoles]);
            abort(403, 'Akses tidak diizinkan untuk role ini.');
        }

        return $next($request);
    }
}
