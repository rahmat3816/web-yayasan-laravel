<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\MutunSetoran;
use App\Models\MutunTarget;
use App\Models\Santri;
use App\Support\TahfizhMutun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MutunSetoranFormController extends Controller
{
    public function create(Request $request): View
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $santriId = (int) $request->query('santri_id');

        $accessibleIds = TahfizhMutun::accessibleSantriIds($user);
        $hasFullScope = TahfizhMutun::userHasManagementAccess($user);

        if (! $santriId) {
            $santriId = $accessibleIds[0] ?? null;
        }

        if (! $santriId || (! $hasFullScope && ! in_array($santriId, $accessibleIds, true))) {
            abort(403, 'Anda tidak memiliki akses ke santri ini.');
        }

        $santri = Santri::with([
                'halaqoh' => fn ($query) => $query->select('halaqoh.id', 'nama_halaqoh'),
                'unit:id,nama_unit',
            ])
            ->whereIn('unit_id', TahfizhMutun::unitIds())
            ->findOrFail($santriId);

        $targets = MutunTarget::with('mutun')
            ->withCount('setorans')
            ->where('santri_id', $santri->id)
            ->orderByDesc('tahun')
            ->orderByDesc('semester')
            ->get();

        return view('filament.pages.setoran-mutun-create', [
            'santri' => $santri,
            'targets' => $targets,
            'accessibleTargets' => $targets->pluck('id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $data = $request->validate([
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'target_id' => ['required', 'integer', 'exists:mutun_targets,id'],
            'tanggal' => ['required', 'date'],
            'nilai_mutqin' => ['required', 'integer', 'between:1,10'],
            'catatan' => ['nullable', 'string'],
        ]);

        $target = MutunTarget::with('mutun')->findOrFail($data['target_id']);

        $accessibleIds = TahfizhMutun::accessibleSantriIds($user);
        $hasFullScope = TahfizhMutun::userHasManagementAccess($user);

        if ($target->santri_id !== (int) $data['santri_id']) {
            abort(403, 'Target tidak sesuai dengan santri.');
        }

        if (! $hasFullScope && ! in_array($target->santri_id, $accessibleIds, true)) {
            abort(403, 'Anda tidak memiliki akses untuk santri ini.');
        }

        if ($target->setorans()->exists()) {
            return back()
                ->withErrors(['target_id' => 'Mutun ini sudah pernah disetorkan.'])
                ->withInput();
        }

        $penilaiId = $user->ensureLinkedGuruId($user->name);

        MutunSetoran::create([
            'target_id' => $target->id,
            'tanggal' => $data['tanggal'],
            'penilai_id' => $penilaiId,
            'nilai_mutqin' => $data['nilai_mutqin'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()
            ->route('filament.admin.pages.tahfizh.mutun.setoran')
            ->with('success', 'Setoran mutun berhasil disimpan.');
    }
}
