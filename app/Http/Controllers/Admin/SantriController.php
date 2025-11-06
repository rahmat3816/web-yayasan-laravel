<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Santri;
use App\Models\Unit;

class SantriController extends Controller
{
    /**
     * 📋 Tampilkan daftar semua santri
     */
    public function index()
    {
        $user = Auth::user();
        $query = Santri::with('unit:id,nama_unit');

        // Jika admin/operator, hanya tampilkan santri unit-nya
        if (in_array(strtolower($user->role), ['admin', 'operator'])) {
            $query->where('unit_id', $user->unit_id);
        }

        $santri = $query->orderBy('nama')->get();

        return view('admin.santri.index', compact('santri'));
    }

    /**
     * ➕ Form Tambah Santri
     */
    public function create()
    {
        $user = Auth::user();

        // Hanya superadmin boleh memilih unit
        $units = [];
        if (strtolower($user->role) === 'superadmin') {
            $units = Unit::select('id', 'nama_unit')->orderBy('nama_unit')->get();
        }

        return view('admin.santri.create', compact('units'));
    }

    /**
     * 💾 Simpan Data Santri Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nisn' => 'nullable|string|max:20|unique:santri,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_masuk' => 'nullable|digits:4',
        ]);

        // Tentukan unit_id otomatis bila bukan superadmin
        if (strtolower($user->role) === 'superadmin') {
            $validated['unit_id'] = $request->validate([
                'unit_id' => 'required|exists:unit,id',
            ])['unit_id'];
        } else {
            $validated['unit_id'] = $user->unit_id;
        }

        $validated['tahun_masuk'] = $validated['tahun_masuk'] ?? date('Y');

        // 🔢 Generate NISY aman
        $validated['nisy'] = DB::transaction(function () {
            $tahun = date('Y');
            $prefix = 'YSY' . date('y');
            $lastNisy = Santri::whereYear('created_at', $tahun)
                ->where('nisy', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('nisy');
            $lastNumber = $lastNisy ? intval(substr($lastNisy, -4)) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            return $prefix . $newNumber;
        });

        Santri::create($validated);

        return redirect()->route('admin.santri.index')
            ->with('success', '✅ Data santri berhasil ditambahkan!');
    }

    /**
     * ✏️ Form Edit Santri
     */
    public function edit($id)
    {
        $user = Auth::user();
        $santri = Santri::findOrFail($id);

        $units = [];
        if (strtolower($user->role) === 'superadmin') {
            $units = Unit::select('id', 'nama_unit')->orderBy('nama_unit')->get();
        }

        return view('admin.santri.edit', compact('santri', 'units'));
    }

    /**
     * 🔄 Update Data Santri
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $santri = Santri::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nisn' => 'nullable|string|max:20|unique:santri,nisn,' . $santri->id,
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_masuk' => 'nullable|digits:4',
        ]);

        // Unit otomatis jika bukan superadmin
        if (strtolower($user->role) === 'superadmin') {
            $validated['unit_id'] = $request->validate([
                'unit_id' => 'required|exists:unit,id',
            ])['unit_id'];
        } else {
            $validated['unit_id'] = $user->unit_id;
        }

        $santri->update($validated);

        return redirect()->route('admin.santri.index')
            ->with('success', '✅ Data santri berhasil diperbarui!');
    }
}
