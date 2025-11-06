<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Guru;
use App\Models\Unit;
use App\Models\User;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Guru::with('unit:id,nama_unit')->orderBy('nama');

        if (in_array(strtolower($user->role ?? ''), ['admin','operator'], true) && $user->unit_id) {
            $q->where('unit_id', $user->unit_id);
        }

        $guru = $q->paginate(20);
        return view('admin.guru.index', compact('guru'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $units = Unit::orderBy('id')->get(['id','nama_unit']);
        if (in_array(strtolower($user->role ?? ''), ['admin','operator'], true) && $user->unit_id) {
            $units = Unit::where('id', $user->unit_id)->get(['id','nama_unit']);
        }

        return view('admin.guru.create', compact('units'));
    }

    public function store(Request $request)
    {
        $userLogin = $request->user();
        $isUnitScoped = in_array(strtolower($userLogin->role ?? ''), ['admin','operator'], true) && $userLogin->unit_id;

        $data = $request->validate([
            'nama'          => ['required','string','max:150'],
            'nip'           => ['nullable','string','max:50', 'unique:guru,nip'],
            'jenis_kelamin' => ['required', Rule::in(['L','P'])],
            'unit_id'       => ['nullable','integer','exists:units,id'],
            'status_aktif'  => ['required', Rule::in(['aktif','nonaktif'])],
            'jabatan'       => ['nullable', Rule::in(['wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri'])],
        ]);

        if ($isUnitScoped) {
            $data['unit_id'] = (int) $userLogin->unit_id;
        }
        if (empty($data['unit_id'])) {
            return back()->withErrors(['unit_id' => 'Unit pendidikan wajib diisi.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $guru = Guru::create([
                'nama'          => $data['nama'],
                'nip'           => $data['nip'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'unit_id'       => $data['unit_id'],
                'status_aktif'  => $data['status_aktif'],
            ]);

            // 🔹 Buat akun user otomatis
            $username = $this->generateUsername2Digits($guru->nama);
            $user = User::create([
                'name'           => $guru->nama,
                'email'          => $username.'@yayasan.local',
                'username'       => $username,
                'password'       => bcrypt('password'),
                'role'           => 'guru',
                'unit_id'        => $guru->unit_id,
                'linked_guru_id' => $guru->id,
            ]);

            // Role default = guru
            if (method_exists($user, 'assignRole') && !$user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            // 🔹 Sinkron jabatan sesuai form
            $this->syncJabatanRole($user, $data['jabatan'] ?? null, $guru->jenis_kelamin);
        });

        return redirect()->route('admin.guru.index')->with('success','Guru berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $guru = Guru::with('unit:id,nama_unit')->findOrFail($id);
        return view('admin.guru.show', compact('guru'));
    }

    public function edit(Request $request, int $id)
    {
        $user = $request->user();
        $guru = Guru::findOrFail($id);

        if (in_array(strtolower($user->role ?? ''), ['admin','operator'], true) && $user->unit_id && (int)$guru->unit_id !== (int)$user->unit_id) {
            abort(403, 'Anda hanya dapat mengedit guru di unit Anda.');
        }

        $units = Unit::orderBy('id')->get(['id','nama_unit']);
        if (in_array(strtolower($user->role ?? ''), ['admin','operator'], true) && $user->unit_id) {
            $units = Unit::where('id', $user->unit_id)->get(['id','nama_unit']);
        }

        // Deteksi jabatan aktif
        $userGuru = User::where('linked_guru_id', $guru->id)->first();
        $jabatanSelected = '';
        if ($userGuru && method_exists($userGuru, 'hasRole')) {
            if ($userGuru->hasRole('koordinator_tahfizh_putra')) $jabatanSelected = 'koordinator_tahfizh_putra';
            elseif ($userGuru->hasRole('koordinator_tahfizh_putri')) $jabatanSelected = 'koordinator_tahfizh_putri';
            elseif ($userGuru->hasRole('wali_kelas')) $jabatanSelected = 'wali_kelas';
        }

        return view('admin.guru.edit', compact('guru','units','jabatanSelected'));
    }

    public function update(Request $request, int $id)
    {
        $userLogin = $request->user();
        $guru = Guru::findOrFail($id);

        if (in_array(strtolower($userLogin->role ?? ''), ['admin','operator'], true) && $userLogin->unit_id && (int)$guru->unit_id !== (int)$userLogin->unit_id) {
            abort(403, 'Anda hanya dapat mengedit guru di unit Anda.');
        }

        $data = $request->validate([
            'nama'          => ['required','string','max:150'],
            'nip'           => ['nullable','string','max:50', Rule::unique('guru','nip')->ignore($guru->id)],
            'jenis_kelamin' => ['required', Rule::in(['L','P'])],
            'unit_id'       => ['nullable','integer','exists:units,id'],
            'status_aktif'  => ['required', Rule::in(['aktif','nonaktif'])],
            'jabatan'       => ['nullable', Rule::in(['wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri'])],
        ]);

        if (in_array(strtolower($userLogin->role ?? ''), ['admin','operator'], true) && $userLogin->unit_id) {
            $data['unit_id'] = (int) $userLogin->unit_id;
        }
        $targetUnit = (int)($data['unit_id'] ?? $guru->unit_id);

        DB::transaction(function () use ($guru, $data, $targetUnit) {
            $guru->update([
                'nama'          => $data['nama'],
                'nip'           => $data['nip'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'unit_id'       => $targetUnit,
                'status_aktif'  => $data['status_aktif'],
            ]);

            $user = User::where('linked_guru_id', $guru->id)->first();

            if (!$user) {
                $username = $this->generateUsername2Digits($guru->nama);
                $user = User::create([
                    'name'           => $guru->nama,
                    'email'          => $username.'@yayasan.local',
                    'username'       => $username,
                    'password'       => bcrypt('password'),
                    'role'           => 'guru',
                    'unit_id'        => $guru->unit_id,
                    'linked_guru_id' => $guru->id,
                ]);
            } else {
                $user->update([
                    'name'    => $guru->nama,
                    'unit_id' => $guru->unit_id,
                ]);

                if (empty($user->username) || str_starts_with($user->username, 'guru.')) {
                    $user->username = $this->generateUsername2Digits($guru->nama);
                }
                if (empty($user->email) || str_starts_with($user->email, 'guru.')) {
                    $user->email = $user->username.'@yayasan.local';
                }
                $user->save();
            }

            if (method_exists($user, 'assignRole') && !$user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            $this->syncJabatanRole($user, $data['jabatan'] ?? null, $guru->jenis_kelamin);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru diperbarui.');
    }

    public function destroy(int $id)
    {
        $guru = Guru::findOrFail($id);

        DB::transaction(function () use ($guru) {
            $user = User::where('linked_guru_id', $guru->id)->first();
            if ($user && method_exists($user, 'removeRole')) {
                foreach (['wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri','guru'] as $r) {
                    if ($user->hasRole($r)) $user->removeRole($r);
                }
            }
            $guru->delete();
        });

        return back()->with('success','Guru dihapus.');
    }

    /** ===============================
     * 🔧 Helpers
     * =============================== */

    private function generateUsername2Digits(string $nama): string
    {
        $nama = trim($nama);
        if ($nama === '') {
            $nama = 'user';
        }

        $first = trim(preg_split('/\s+/', $nama)[0] ?? '');
        $base  = strtolower(preg_replace('/[^a-z0-9]/i', '', $first));
        if ($base === '') {
            $base = 'user';
        }

        $existing = \App\Models\User::where('username', 'like', $base.'%')->pluck('username')->all();

        $used = [];
        foreach ($existing as $u) {
            if (preg_match('/^'.preg_quote($base,'/').'(\d{2})$/', $u, $m)) {
                $used[(int)$m[1]] = true;
            }
        }

        $candidates = [];
        for ($i = 1; $i <= 99; $i++) {
            if (!isset($used[$i])) {
                $candidates[] = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            }
        }
        shuffle($candidates);

        foreach ($candidates as $suffix) {
            $username = $base.$suffix;
            if (!\App\Models\User::where('username', $username)->exists()) {
                return $username;
            }
        }

        return $base . str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
    }

    private function syncJabatanRole($user, ?string $jabatan, ?string $jenisKelamin)
    {
        if (!$user) {
            return;
        }

        $validRoles = [
            'guru',
            'wali_kelas',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
        ];

        $jabatan = strtolower($jabatan ?? 'guru');
        if (!in_array($jabatan, $validRoles, true)) {
            $jabatan = 'guru';
        }

        $user->update(['role' => $jabatan]);

        if ($user->hasAnyRole($validRoles)) {
            $user->syncRoles([$jabatan]);
        } else {
            $user->assignRole($jabatan);
        }
    }
}
