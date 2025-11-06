<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUnitAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Superadmin bebas akses semua
        if ($user && strtolower($user->role) === 'superadmin') {
            return $next($request);
        }

        // Admin & Operator hanya boleh lihat unit sendiri
        if (in_array(strtolower($user->role), ['admin', 'operator'])) {
            $request->attributes->add(['limit_unit_id' => $user->unit_id]);
        }

        return $next($request);
    }
}
