<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use App\Models\HaditsSetoran;
use App\Models\HaditsTarget;
use App\Models\Santri;
use App\Support\TahfizhHadits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HaditsSetoranFormController extends Controller
{
    public function create(Request $request): View
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $santriId = (int) $request->query('santri_id');

        $accessibleIds = TahfizhHadits::accessibleSantriIds($user);
        $hasFullScope = TahfizhHadits::userHasFullSantriScope($user);

        if (! $santriId) {
            $santriId = $accessibleIds[0] ?? null;
        }

        if (! $santriId || (! $hasFullScope && ! in_array($santriId, $accessibleIds, true))) {
            abort(403, 'Anda tidak memiliki akses ke santri ini.');
        }

        $santri = Santri::with(['halaqoh:id,nama_halaqoh', 'unit:id,nama_unit'])
            ->whereIn('unit_id', TahfizhHadits::unitIds())
            ->findOrFail($santriId);

        $targets = HaditsTarget::with('hadits')
            ->withCount('setorans')
            ->where('santri_id', $santri->id)
            ->orderByDesc('tahun')
            ->orderByDesc('semester')
            ->get();

        return view('filament.pages.setoran-hadits-create', [
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
            'target_id' => ['required', 'integer', 'exists:hadits_targets,id'],
            'tanggal' => ['required', 'date'],
            'nilai_mutqin' => ['required', 'integer', 'between:1,10'],
            'catatan' => ['nullable', 'string'],
        ]);

        $target = HaditsTarget::with('hadits')->findOrFail($data['target_id']);

        $accessibleIds = TahfizhHadits::accessibleSantriIds($user);
        $hasFullScope = TahfizhHadits::userHasFullSantriScope($user);

        if ($target->santri_id !== (int) $data['santri_id']) {
            abort(403, 'Target tidak sesuai dengan santri.');
        }

        if (! $hasFullScope && ! in_array($target->santri_id, $accessibleIds, true)) {
            abort(403, 'Anda tidak memiliki akses untuk santri ini.');
        }

        if ($target->setorans()->exists()) {
            return back()
                ->withErrors(['target_id' => 'Hadits ini sudah pernah disetorkan.'])
                ->withInput();
        }

        $setoran = HaditsSetoran::create([
            'target_id' => $target->id,
            'tanggal' => $data['tanggal'],
            'penilai_id' => $user->ensureLinkedGuruId($user->name),
            'nilai_mutqin' => $data['nilai_mutqin'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()
            ->route('filament.admin.pages.tahfizh.hadits.setoran')
            ->with('success', 'Setoran hadits berhasil disimpan.');
    }
}
