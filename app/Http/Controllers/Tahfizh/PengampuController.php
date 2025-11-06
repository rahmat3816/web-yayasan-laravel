<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Halaqoh;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\Unit;

class PengampuController extends Controller
{
    protected function userUnitId(): ?int
    {
        return Auth::user()?->unit_id;
    }

    protected function isKoordinator(): bool
    {
        $u = Auth::user();
        return $u && (
            (method_exists($u, 'hasRole') && ($u->hasRole('koordinator_tahfizh_putra') || $u->hasRole('koordinator_tahfizh_putri')))
            || in_array(strtolower($u->role ?? ''), ['koordinator_tahfizh_putra','koordinator_tahfizh_putri'], true)
        );
    }

    protected function abortIfDifferentUnit(?int $unitId): void
    {
        if ($this->isKoordinator()) {
            $my = $this->userUnitId();
            if ($my && $unitId && $my !== (int)$unitId) {
                abort(403, 'Akses unit tidak diizinkan.');
            }
        }
    }

    // Daftar halaqoh
    public function index()
    {
        $q = Halaqoh::with(['guru:id,nama,unit_id','santri:id,nama','unit:id,nama_unit'])
            ->orderByDesc('id');

        if ($this->isKoordinator()) {
            $q->where('unit_id', $this->userUnitId());
        }

        $halaqoh = $q->get();
        return view('tahfizh.halaqoh.index', compact('halaqoh'));
    }

    // Form buat baru
    public function create()
    {
        if ($this->isKoordinator()) {
            $unit = Unit::where('id', $this->userUnitId())->get(['id','nama_unit']);
        } else {
            $unit = Unit::orderBy('id')->get(['id','nama_unit']);
        }
        return view('tahfizh.halaqoh.create', ['units' => $unit]);
    }

    // Simpan halaqoh baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'   => ['nullable','integer','exists:units,id'],
            'guru_id'   => ['required','integer','exists:guru,id'],
            'keterangan'=> ['nullable','string','max:255'],
            'santri'    => ['nullable','array'],
            'santri.*'  => ['integer','exists:santri,id'],
        ]);

        // Kunci unit untuk koordinator
        if ($this->isKoordinator()) {
            $data['unit_id'] = $this->userUnitId();
        }

        $this->abortIfDifferentUnit($data['unit_id'] ?? null);
        if (empty($data['unit_id'])) {
            return back()->withErrors(['unit_id' => 'Unit wajib diisi.'])->withInput();
        }

        // Cek 1 guru 1 halaqoh
        if (Halaqoh::where('guru_id', $data['guru_id'])->exists()) {
            return back()->withErrors(['guru_id' => 'Guru ini sudah memiliki halaqoh.'])->withInput();
        }

        // Cek guru & santri harus 1 unit
        $guru = Guru::findOrFail($data['guru_id']);
        if ((int)$guru->unit_id !== (int)$data['unit_id']) {
            return back()->withErrors(['guru_id' => 'Guru tidak berasal dari unit yang sama.'])->withInput();
        }

        $santriIds = $data['santri'] ?? [];
        if (!empty($santriIds)) {
            $santriUnitMismatch = Santri::whereIn('id', $santriIds)
                ->where('unit_id', '!=', $data['unit_id'])
                ->pluck('nama');
            if ($santriUnitMismatch->isNotEmpty()) {
                return back()->withErrors(['santri' => 'Terdapat santri dari unit lain: '.$santriUnitMismatch->implode(', ')])->withInput();
            }
            $sudahTerpakai = DB::table('halaqoh_santri')->whereIn('santri_id', $santriIds)->pluck('santri_id')->all();
            if (!empty($sudahTerpakai)) {
                $nama = Santri::whereIn('id', $sudahTerpakai)->pluck('nama')->implode(', ');
                return back()->withErrors(['santri' => 'Beberapa santri sudah tergabung di halaqoh lain: '.$nama])->withInput();
            }
        }

        DB::transaction(function () use ($data, $santriIds) {
            $h = Halaqoh::create([
                'unit_id'   => $data['unit_id'],
                'guru_id'   => $data['guru_id'],
                'keterangan'=> $data['keterangan'] ?? null,
                'nama_halaqoh' => $this->generateNamaHalaqoh($data['unit_id'], $data['guru_id']),
            ]);

            if (!empty($santriIds)) {
                $rows = [];
                foreach ($santriIds as $sid) {
                    $rows[] = ['halaqoh_id' => $h->id, 'santri_id' => $sid];
                }
                DB::table('halaqoh_santri')->insert($rows);
            }
        });

        return redirect()->route('tahfizh.halaqoh.index')->with('success', 'Halaqoh baru berhasil dibuat.');
    }

    // Edit pengampu
    public function edit(int $id)
    {
        $h = Halaqoh::with(['guru:id,nama,unit_id', 'santri:id,nama', 'unit:id,nama_unit'])->findOrFail($id);
        $this->abortIfDifferentUnit($h->unit_id);

        if ($this->isKoordinator()) {
            $units = Unit::where('id', $this->userUnitId())->get(['id','nama_unit']);
            $gurus = Guru::where('unit_id', $h->unit_id)->orderBy('nama')->get(['id','nama']);
            $santriAll = Santri::where('unit_id', $h->unit_id)->orderBy('nama')->get(['id','nama']);
        } else {
            $units = Unit::orderBy('id')->get(['id','nama_unit']);
            $gurus = Guru::where('unit_id', $h->unit_id)->orderBy('nama')->get(['id','nama']);
            $santriAll = Santri::where('unit_id', $h->unit_id)->orderBy('nama')->get(['id','nama']);
        }

        $santriTerpilih = $h->santri->pluck('id')->all();

        return view('tahfizh.halaqoh.create', compact('units'))
            ->with([
                'edit' => true,
                'current' => $h,
                'gurus' => $gurus,
                'santriAll' => $santriAll,
                'santriTerpilih' => $santriTerpilih,
            ]);
    }

    public function update(Request $request, int $id)
    {
        $h = Halaqoh::findOrFail($id);
        $this->abortIfDifferentUnit($h->unit_id);

        $data = $request->validate([
            'unit_id'   => ['nullable','integer','exists:units,id'],
            'guru_id'   => ['required','integer','exists:guru,id'],
            'keterangan'=> ['nullable','string','max:255'],
            'santri'    => ['nullable','array'],
            'santri.*'  => ['integer','exists:santri,id'],
        ]);

        // Koordinator tidak boleh pindah unit
        if ($this->isKoordinator()) {
            $data['unit_id'] = $this->userUnitId();
        }
        $this->abortIfDifferentUnit($data['unit_id'] ?? $h->unit_id);

        // Larang mengubah ke guru lain yang sudah punya halaqoh
        if ($h->guru_id != $data['guru_id'] && Halaqoh::where('guru_id', $data['guru_id'])->exists()) {
            return back()->withErrors(['guru_id' => 'Guru yang dipilih sudah memiliki halaqoh.'])->withInput();
        }

        // Pastikan konsistensi unit
        $guru = Guru::findOrFail($data['guru_id']);
        $targetUnit = (int)($data['unit_id'] ?? $h->unit_id);
        if ((int)$guru->unit_id !== $targetUnit) {
            return back()->withErrors(['guru_id' => 'Guru tidak berasal dari unit yang sama.'])->withInput();
        }

        $santriIds = $data['santri'] ?? [];

        if (!empty($santriIds)) {
            $mismatch = Santri::whereIn('id', $santriIds)->where('unit_id', '!=', $targetUnit)->pluck('nama');
            if ($mismatch->isNotEmpty()) {
                return back()->withErrors(['santri' => 'Terdapat santri dari unit lain: '.$mismatch->implode(', ')])->withInput();
            }
            $sudahTerpakai = DB::table('halaqoh_santri')
                ->whereIn('santri_id', $santriIds)
                ->where('halaqoh_id', '!=', $h->id)
                ->pluck('santri_id')->all();
            if (!empty($sudahTerpakai)) {
                $nama = Santri::whereIn('id', $sudahTerpakai)->pluck('nama')->implode(', ');
                return back()->withErrors(['santri' => 'Beberapa santri sudah tergabung di halaqoh lain: '.$nama])->withInput();
            }
        }

        DB::transaction(function () use ($h, $data, $santriIds, $targetUnit) {
            $h->update([
                'unit_id'   => $targetUnit,
                'guru_id'   => $data['guru_id'],
                'keterangan'=> $data['keterangan'] ?? null,
            ]);

            DB::table('halaqoh_santri')->where('halaqoh_id', $h->id)->delete();
            if (!empty($santriIds)) {
                $rows = [];
                foreach ($santriIds as $sid) {
                    $rows[] = ['halaqoh_id' => $h->id, 'santri_id' => $sid];
                }
                DB::table('halaqoh_santri')->insert($rows);
            }
        });

        return redirect()->route('tahfizh.halaqoh.index')->with('success', 'Halaqoh berhasil diperbarui.');
    }

    // ===============================
    // AJAX Dropdown helper
    // ===============================
    public function getGuruByUnit(int $unitId)
    {
        $this->abortIfDifferentUnit($unitId);
        $data = Guru::where('unit_id', $unitId)->orderBy('nama')->get(['id','nama']);
        return response()->json($data);
    }

    public function getSantriByUnit(int $unitId)
    {
        $this->abortIfDifferentUnit($unitId);

        $sudah = DB::table('halaqoh_santri')->pluck('santri_id')->all();
        $data = Santri::where('unit_id', $unitId)
            ->when(!empty($sudah), fn($q) => $q->whereNotIn('id', $sudah))
            ->orderBy('nama')
            ->get(['id','nama']);

        return response()->json($data);
    }

    protected function generateNamaHalaqoh(int $unitId, int $guruId): string
    {
        $guru = Guru::find($guruId);
        $unit = Unit::find($unitId);
        return trim(($unit?->kode_unit ?? 'Unit '.$unitId) . ' - ' . ($guru?->nama ?? 'Guru '.$guruId));
    }
}
