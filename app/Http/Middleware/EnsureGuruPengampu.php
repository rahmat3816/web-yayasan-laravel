<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Halaqoh;
use App\Models\Santri;

class EnsureGuruPengampu
{
    /**
     * Memastikan guru adalah pengampu halaqoh dan santri yang diakses adalah miliknya.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $linkedGuruId = (int) ($user->linked_guru_id ?? 0);

        if (!$linkedGuruId && method_exists($user, 'ensureLinkedGuruId')) {
            $linkedGuruId = (int) ($user->ensureLinkedGuruId() ?? 0);
        }

        // 🟢 Superadmin tetap diizinkan
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // Jika route mengandung parameter santriId → validasi santri-nya
        $santriId = $request->route('santriId');
        if ($santriId) {
            $santri = Santri::find($santriId);
            if (!$santri) {
                return redirect()
                    ->route('guru.dashboard')
                    ->with('error', 'Santri tidak ditemukan.');
            }

            $halaqoh = Halaqoh::where('guru_id', $linkedGuruId)->first();
            if (!$halaqoh) {
                return redirect()
                    ->route('guru.dashboard')
                    ->with('error', 'Anda belum ditunjuk sebagai pengampu halaqoh.');
            }

            $isMember = $halaqoh->santri()->where('santri.id', $santriId)->exists();
            if (!$isMember) {
                return redirect()
                    ->route('guru.dashboard')
                    ->with('error', 'Santri ini tidak berada dalam halaqoh Anda.');
            }
        } else {
            // Akses halaman umum (index / rekap)
            $hasHalaqoh = Halaqoh::where('guru_id', $linkedGuruId)->exists();
            if (!$hasHalaqoh) {
                return redirect()
                    ->route('guru.dashboard')
                    ->with('error', 'Anda belum ditunjuk sebagai pengampu halaqoh.');
            }
        }

        return $next($request);
    }
}
