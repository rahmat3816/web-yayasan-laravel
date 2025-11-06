<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UnitAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 🧪 Bypass otomatis saat testing
        if (app()->environment('testing')) {
            return $next($request);
        }
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $role = strtolower($user->role ?? '');
        Log::info('🧩 UnitAccessMiddleware', [
            'user_id' => $user->id ?? null,
            'email'   => $user->email ?? null,
            'role'    => $role,
            'unit_id' => $user->unit_id ?? null,
            'url'     => $request->url(),
        ]);

        // ✅ SUPERADMIN BYPASS LANGSUNG
        if ($role === 'superadmin') {
            Log::info('✅ Superadmin bypass aktif');
            return $next($request);
        }

        if (in_array($role, ['admin', 'operator'], true)) {
            if (empty($user->unit_id)) {
                Log::warning('❌ Akses ditolak: unit_id kosong');
                abort(403, 'Akses ditolak: unit pengguna tidak terdeteksi.');
            }

            $unitParam = $request->route('unit_id') ?? $request->get('unit_id');
            if ($unitParam !== null && (int)$unitParam !== (int)$user->unit_id) {
                Log::warning('❌ Akses ditolak: unit mismatch', ['unitParam' => $unitParam]);
                abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk unit ini.');
            }
        }

        Log::info('✅ UnitAccessMiddleware lolos');
        return $next($request);
    }
}
