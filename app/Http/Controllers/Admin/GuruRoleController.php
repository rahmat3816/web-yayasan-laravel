<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Guru;
use App\Models\User;
use App\Models\Unit;

class GuruRoleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $guru = Guru::with('user:id,username,role,unit_id')
            ->when($q, fn($qq) => $qq->where('nama', 'like', '%'.$q.'%'))
            ->orderBy('nama')
            ->paginate(20);

        return view('admin.guru.jabatan.index', compact('guru', 'q'));
    }

    public function edit(int $guruId)
    {
        $g = Guru::with('user')->findOrFail($guruId);
        $units = Unit::orderBy('id')->get(['id','nama_unit']);
        $roles = [
            'guru' => 'Guru',
            'koordinator_tahfizh_putra' => 'Koordinator Tahfizh Putra',
            'koordinator_tahfizh_putri' => 'Koordinator Tahfizh Putri',
        ];

        return view('admin.guru.jabatan.edit', compact('g', 'units', 'roles'));
    }

    public function update(Request $request, int $guruId)
    {
        $g = Guru::with('user')->findOrFail($guruId);

        $data = $request->validate([
            'role'    => ['required', Rule::in(['guru','koordinator_tahfizh_putra','koordinator_tahfizh_putri'])],
            'unit_id' => ['nullable','integer','exists:units,id'],
        ]);

        $user = $g->user ?: $this->ensureUserForGuru($g);

        // Jika menjadi koordinator, wajib unit_id
        if ($data['role'] !== 'guru' && empty($data['unit_id'])) {
            return back()->withErrors(['unit_id' => 'Unit wajib dipilih untuk koordinator.'])->withInput();
        }

        // Update kolom role + unit
        $user->role = $data['role'];
        if (!empty($data['unit_id'])) {
            $user->unit_id = $data['unit_id'];
        }
        $user->save();

        // Sinkron Spatie role jika ada
        if (method_exists($user, 'syncRoles')) {
            try {
                $user->syncRoles([$data['role']]);
            } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.guru.jabatan.index')->with('success', 'Jabatan guru berhasil diperbarui.');
    }

    protected function ensureUserForGuru(Guru $g): User
    {
        // Buat user jika belum ada, minimal untuk bisa diberi role
        $username = \Str::slug($g->nama, '') ?: 'guru';
        $base = $username; $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.$i++;
        }

        return User::create([
            'name' => $g->nama,
            'email' => ($g->email ?? null) ?: $username.'@yayasan.local',
            'username' => $username,
            'password' => \Hash::make('password'),
            'role' => 'guru',
            'unit_id' => $g->unit_id,
            'linked_guru_id' => $g->id,
        ]);
    }
}
