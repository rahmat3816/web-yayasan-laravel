<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * 📋 Tampilkan daftar unit
     */
    public function index()
    {
        $units = Unit::orderBy('nama_unit')->get();
        return view('admin.unit.index', compact('units'));
    }

    /**
     * ➕ Form Tambah Unit
     */
    public function create()
    {
        return view('admin.unit.create');
    }

    /**
     * 💾 Simpan Data Unit Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:100|unique:unit,nama_unit',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.unit.index')
            ->with('success', '✅ Data unit berhasil ditambahkan!');
    }

    /**
     * 👁️ Detail Unit
     */
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit.show', compact('units'));
    }

    /**
     * ✏️ Form Edit Unit
     */
    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.unit.edit', compact('units'));
    }

    /**
     * 🔄 Update Data Unit
     */
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'nama_unit' => 'required|string|max:100|unique:unit,nama_unit,' . $unit->id,
        ]);

        $unit->update($validated);

        return redirect()->route('admin.unit.index')
            ->with('success', '✅ Data unit berhasil diperbarui!');
    }

    /**
     * 🗑️ Hapus Unit
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('admin.unit.index')
            ->with('success', '🗑️ Data unit berhasil dihapus!');
    }
}
