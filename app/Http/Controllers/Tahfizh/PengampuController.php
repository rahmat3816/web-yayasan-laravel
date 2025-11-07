<?php

namespace App\Http\Controllers\Tahfizh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Halaqoh;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengampuController extends Controller
{
    /**
     * 📋 Daftar Halaqoh
     */
    public function index()
    {
        $user = auth()->user();

        // Tentukan jenis kelamin sesuai role
        $gender = null;
        if ($user->hasRole('koordinator_tahfizh_putra')) {
            $gender = 'L';
        } elseif ($user->hasRole('koordinator_tahfizh_putri')) {
            $gender = 'P';
        }

        $halaqoh = Halaqoh::with(['guru:id,nama,jenis_kelamin,unit_id', 'unit:id,nama_unit'])
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('unit_id', $user->unit_id))
            ->when($gender, fn($q) =>
                $q->whereHas('guru', fn($g) => $g->where('jenis_kelamin', $gender))
            )
            ->orderBy('nama_halaqoh')
            ->get();

        return view('tahfizh.halaqoh.index', compact('halaqoh'));
    }

    /**
     * ➕ Form Tambah Halaqoh (koordinator tahfizh)
     */
    public function create()
    {
        $user = auth()->user();

        // Tentukan jenis kelamin berdasarkan role
        $gender = null;
        if ($user->hasRole('koordinator_tahfizh_putra')) {
            $gender = 'L';
        } elseif ($user->hasRole('koordinator_tahfizh_putri')) {
            $gender = 'P';
        }

        // 🔹 Hanya guru aktif tanpa halaqoh di unit dan gender yang sesuai
        $guru = Guru::select('id', 'nama', 'unit_id', 'jenis_kelamin')
            ->whereDoesntHave('halaqoh')
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('unit_id', $user->unit_id))
            ->when($gender, fn($q) => $q->where('jenis_kelamin', $gender))
            ->orderBy('nama')
            ->get();

        // 🔹 Unit dikunci sesuai user
        $units = Unit::select('id', 'nama_unit')
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('id', $user->unit_id))
            ->orderBy('nama_unit')
            ->get();

        // 🔒 Tambahan keamanan: jika tidak ada guru sesuai gender
        if ($guru->isEmpty()) {
            return redirect()->route('tahfizh.halaqoh.index')
                ->with('error', 'Tidak ada guru sesuai kriteria (unit & jenis kelamin).');
        }

        return view('tahfizh.halaqoh.create', compact('guru', 'units'));
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

        $unitId = $guru->unit_id;
        $gender = strtoupper($guru->jenis_kelamin ?? '');

        // 🔹 Ambil santri sesuai unit & jenis kelamin guru
        $santriQuery = Santri::select('id', 'nama', 'nisy', 'jenis_kelamin', 'unit_id')
            ->where('unit_id', $unitId)
            ->whereRaw('UPPER(jenis_kelamin) = ?', [$gender])
            ->orderBy('nama');

        // 🔹 Kecualikan santri yang sudah tergabung di halaqoh lain
        $santriQuery->whereDoesntHave('halaqoh');

        // 🔹 Jika mode edit, izinkan santri yang sudah tergabung di halaqoh ini
        if ($request->filled('halaqoh_id')) {
            $halaqohId = (int) $request->halaqoh_id;
            $santriQuery->orWhereHas('halaqoh', function ($q) use ($halaqohId) {
                $q->where('halaqoh.id', $halaqohId);
            });
        }

        $santri = $santriQuery->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'nisy' => $s->nisy,
            ];
        });

        return response()->json($santri);
    }

    // 💾 Simpan Data Halaqoh (tidak diubah dulu)
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'keterangan' => 'nullable|string|max:255',
            'santri_ids' => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
        ]);

        $validated['unit_id'] = $user->unit_id;

        if (Halaqoh::where('guru_id', $validated['guru_id'])->exists()) {
            return back()->withErrors(['guru_id' => 'Guru ini sudah memiliki halaqoh.'])->withInput();
        }

        $duplikat = Santri::whereIn('id', $validated['santri_ids'] ?? [])
            ->whereHas('halaqoh')
            ->pluck('nama')
            ->toArray();

        if (!empty($duplikat)) {
            return back()->withErrors(['santri_ids' => 'Santri berikut sudah di halaqoh lain: ' . implode(', ', $duplikat)])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $halaqoh = Halaqoh::create([
                'nama_halaqoh' => 'Halaqoh ' . now()->format('His'),
                'guru_id' => $validated['guru_id'],
                'unit_id' => $validated['unit_id'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            if (!empty($validated['santri_ids'])) {
                $halaqoh->santri()->sync($validated['santri_ids']);
            }
        });

        return redirect()->route('tahfizh.halaqoh.index')
            ->with('success', '✅ Halaqoh baru berhasil dibuat!');
    }

    /**
     * ✏️ Form Edit Halaqoh
     */
    public function edit($id)
    {
        $user = auth()->user();
        $halaqoh = Halaqoh::with(['guru', 'santri', 'unit'])->findOrFail($id);

        // Tentukan gender sesuai role
        $gender = null;
        if ($user->hasRole('koordinator_tahfizh_putra')) {
            $gender = 'L';
        } elseif ($user->hasRole('koordinator_tahfizh_putri')) {
            $gender = 'P';
        }

        // 🔒 Batasi akses halaqoh lawan jenis (keamanan tambahan)
        if ($gender && ($halaqoh->guru->jenis_kelamin ?? null) !== $gender) {
            abort(403, 'Anda tidak diizinkan mengakses halaqoh ini.');
        }

        // 🔹 Daftar guru yang bisa dipilih (belum punya halaqoh atau guru ini sendiri)
        $guru = Guru::select('id', 'nama', 'unit_id', 'jenis_kelamin')
            ->where(function ($q) use ($halaqoh) {
                $q->whereDoesntHave('halaqoh')
                ->orWhere('id', $halaqoh->guru_id);
            })
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('unit_id', $user->unit_id))
            ->when($gender, fn($q) => $q->where('jenis_kelamin', $gender))
            ->orderBy('nama')
            ->get();

        // 🔹 Unit (superadmin bisa lihat semua)
        $units = Unit::select('id', 'nama_unit')
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('id', $user->unit_id))
            ->orderBy('nama_unit')
            ->get();

        return view('tahfizh.halaqoh.edit', compact('halaqoh', 'guru', 'units'));
    }

    /**
     * 💾 Update Data Halaqoh
     */
    public function update(Request $request, $id)
    {
        $halaqoh = Halaqoh::findOrFail($id);
        $user = auth()->user();

        $validated = $request->validate([
            'nama_halaqoh' => 'required|string|max:100',
            'guru_id' => 'required|exists:guru,id',
            'keterangan' => 'nullable|string|max:255',
            'santri_ids' => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
        ]);

        // 🔒 Cegah guru ganda
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
            return back()->withErrors([
                'santri_ids' => 'Santri berikut sudah di halaqoh lain: ' . implode(', ', $duplikat)
            ])->withInput();
        }

        // 🔹 Update & sinkronisasi santri
        DB::transaction(function () use ($halaqoh, $validated, $user) {
            $halaqoh->update([
                'nama_halaqoh' => $validated['nama_halaqoh'],
                'guru_id' => $validated['guru_id'],
                'unit_id' => $user->unit_id,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $halaqoh->santri()->sync($validated['santri_ids'] ?? []);
        });

        return redirect()->route('tahfizh.halaqoh.index')
            ->with('success', '✅ Data halaqoh berhasil diperbarui!');
    }

}
