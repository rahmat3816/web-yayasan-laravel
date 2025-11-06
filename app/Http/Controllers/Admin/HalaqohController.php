<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Halaqoh;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class HalaqohController extends Controller
{
    /**
     * 📋 Daftar Halaqoh
     */
    public function index()
    {
        $user = auth()->user();

        $halaqoh = Halaqoh::with(['guru:id,nama', 'unit:id,nama_unit'])
            ->when(!in_array(strtolower($user->role), ['superadmin']), function ($query) use ($user) {
                $query->where('unit_id', $user->unit_id);
            })
            ->orderBy('nama_halaqoh')
            ->get();

        return view('admin.halaqoh.index', compact('halaqoh'));
    }

    /**
     * ➕ Form Tambah Halaqoh
     */
    public function create()
    {
        $user = auth()->user();

        // Hanya guru yang belum punya halaqoh
        $guru = Guru::select('id', 'nama', 'unit_id', 'jenis_kelamin')
            ->whereDoesntHave('halaqoh')
            ->when(!in_array(strtolower($user->role), ['superadmin']), function ($query) use ($user) {
                $query->where('unit_id', $user->unit_id);
            })
            ->orderBy('nama')
            ->get();

        $units = Unit::select('id', 'nama_unit')->orderBy('nama_unit')->get();

        return view('admin.halaqoh.create', compact('guru', 'units'));
    }

    /**
     * 📡 AJAX - Ambil Santri berdasarkan Guru
     */
    public function getSantriByGuru($guruId, Request $request)
    {
        $guru = Guru::with('unit:id,nama_unit')->find($guruId);
        if (!$guru) {
            return response()->json(['error' => 'Guru tidak ditemukan.'], 404);
        }

        // Filter santri sesuai unit & jenis kelamin guru
        $santriQuery = Santri::select('id', 'nama', 'nisy', 'jenis_kelamin', 'unit_id')
            ->where('unit_id', $guru->unit_id)
            ->where('jenis_kelamin', $guru->jenis_kelamin)
            ->whereDoesntHave('halaqoh') // hanya santri yang belum punya halaqoh
            ->orderBy('nama');

        $selectedIds = [];
        if ($request->filled('halaqoh_id')) {
            $halaqoh = Halaqoh::with('santri')->find($request->halaqoh_id);
            if ($halaqoh) {
                $selectedIds = $halaqoh->santri->pluck('id')->toArray();
            }
        }

        $santri = $santriQuery->get()->map(function ($s) use ($selectedIds) {
            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'nisy' => $s->nisy,
                'terpilih' => in_array($s->id, $selectedIds),
            ];
        });

        return response()->json($santri);
    }

    /**
     * 💾 Simpan Data Halaqoh
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nama_halaqoh' => 'required|string|max:100|unique:halaqoh,nama_halaqoh',
            'guru_id' => 'required|exists:guru,id',
            'unit_id' => 'nullable|exists:unit,id',
            'keterangan' => 'nullable|string|max:255',
            'santri_ids' => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
        ]);

        // 🔒 Cegah 1 guru punya lebih dari 1 halaqoh
        if (Halaqoh::where('guru_id', $validated['guru_id'])->exists()) {
            return back()->withErrors(['guru_id' => 'Guru ini sudah memiliki halaqoh.'])->withInput();
        }

        // 🔒 Cegah santri bergabung ke lebih dari 1 halaqoh
        $duplikat = Santri::whereIn('id', $validated['santri_ids'] ?? [])
            ->whereHas('halaqoh')
            ->pluck('nama')
            ->toArray();

        if (!empty($duplikat)) {
            return back()->withErrors(['santri_ids' => 'Santri berikut sudah terdaftar di halaqoh lain: ' . implode(', ', $duplikat)])->withInput();
        }

        if (!in_array(strtolower($user->role), ['superadmin'])) {
            $validated['unit_id'] = $user->unit_id;
        }

        DB::transaction(function () use ($validated) {
            $halaqoh = Halaqoh::create($validated);
            if (!empty($validated['santri_ids'])) {
                $halaqoh->santri()->sync($validated['santri_ids']);
            }
        });

        return redirect()->route('admin.halaqoh.index')
            ->with('success', '✅ Data halaqoh berhasil ditambahkan!');
    }

    /**
     * ✏️ Form Edit Halaqoh
     */
    public function edit($id)
    {
        $user = auth()->user();
        $halaqoh = Halaqoh::with('santri')->findOrFail($id);

        $guru = Guru::select('id', 'nama', 'unit_id', 'jenis_kelamin')
            ->where(function ($query) use ($halaqoh) {
                $query->whereDoesntHave('halaqoh')
                      ->orWhere('id', $halaqoh->guru_id);
            })
            ->when(!in_array(strtolower($user->role), ['superadmin']), function ($query) use ($user) {
                $query->where('unit_id', $user->unit_id);
            })
            ->orderBy('nama')
            ->get();

        $units = Unit::select('id', 'nama_unit')->orderBy('nama_unit')->get();

        return view('admin.halaqoh.edit', compact('halaqoh', 'guru', 'units'));
    }

    /**
     * 🔄 Update Data Halaqoh
     */
    public function update(Request $request, $id)
    {
        $halaqoh = Halaqoh::findOrFail($id);

        $validated = $request->validate([
            'nama_halaqoh' => 'required|string|max:100|unique:halaqoh,nama_halaqoh,' . $halaqoh->id,
            'guru_id' => 'required|exists:guru,id',
            'unit_id' => 'nullable|exists:unit,id',
            'keterangan' => 'nullable|string|max:255',
            'santri_ids' => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
        ]);

        // 🔒 Cegah 1 guru memiliki lebih dari 1 halaqoh
        if (Halaqoh::where('guru_id', $validated['guru_id'])->where('id', '!=', $halaqoh->id)->exists()) {
            return back()->withErrors(['guru_id' => 'Guru ini sudah memiliki halaqoh lain.'])->withInput();
        }

        // 🔒 Cegah santri duplikat di halaqoh lain
        $duplikat = Santri::whereIn('id', $validated['santri_ids'] ?? [])
            ->whereHas('halaqoh', function ($q) use ($halaqoh) {
                $q->where('halaqoh.id', '!=', $halaqoh->id);
            })
            ->pluck('nama')
            ->toArray();

        if (!empty($duplikat)) {
            return back()->withErrors(['santri_ids' => 'Santri berikut sudah terdaftar di halaqoh lain: ' . implode(', ', $duplikat)])->withInput();
        }

        DB::transaction(function () use ($halaqoh, $validated) {
            $halaqoh->update($validated);
            $halaqoh->santri()->sync($validated['santri_ids'] ?? []);
        });

        return redirect()->route('admin.halaqoh.index')
            ->with('success', '✅ Data halaqoh berhasil diperbarui!');
    }

    /**
     * 🗑️ Hapus Data Halaqoh
     */
    public function destroy($id)
    {
        $halaqoh = Halaqoh::findOrFail($id);
        $halaqoh->santri()->detach();
        $halaqoh->delete();

        return redirect()->route('admin.halaqoh.index')
            ->with('success', '🗑️ Data halaqoh berhasil dihapus!');
    }
}
