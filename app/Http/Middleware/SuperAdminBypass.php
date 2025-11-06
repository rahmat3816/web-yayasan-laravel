<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminBypass
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // ✅ Global bypass: superadmin boleh melewati semua pembatasan
        if ($user && strtolower($user->role ?? '') === 'superadmin') {
            return $next($request);
        }
        return $next($request);
    }
}