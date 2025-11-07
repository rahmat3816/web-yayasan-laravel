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
    // List semua guru, dengan paginasi
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Guru::with('unit:id,nama_unit')->orderBy('nama');

        if ($this->isUnitScoped($user)) {
            $query->where('unit_id', $user->unit_id);
        }

        $guru = $query->paginate(20);
        return view('admin.guru.index', compact('guru'));
    }

    // Tampilkan form create
    public function create(Request $request)
    {
        $user = $request->user();
        if ($this->isUnitScoped($user)) {
            $units = Unit::where('id', $user->unit_id)
                         ->get(['id','nama_unit']);
        } else {
            $units = Unit::orderBy('id')
                         ->get(['id','nama_unit']);
        }

        return view('admin.guru.create', compact('units'));
    }

    // Simpan guru baru
    public function store(Request $request)
    {
        $userLogin = $request->user();
        $unitScoped = $this->isUnitScoped($userLogin);

        $data = $request->validate([
            'nama'          => ['required','string','max:150'],
            'nip'           => ['nullable','string','max:50', Rule::unique('guru','nip')],
            'jenis_kelamin' => ['required', Rule::in(['L','P'])],
            'unit_id'       => [$unitScoped ? 'required' : 'nullable','integer','exists:units,id'],
            'status_aktif'  => ['required', Rule::in(['aktif','nonaktif'])],
            'jabatan'       => ['nullable', Rule::in(self::JABATAN_LIST())],
        ]);

        if ($unitScoped) {
            $data['unit_id'] = (int) $userLogin->unit_id;
        }

        // Pastikan unit_id diberikan
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

            // Buat akun user otomatis
            $user = $this->createUserForGuru($guru);

            // Sinkron jabatan sesuai form
            $this->syncJabatanRole($user, $data['jabatan'] ?? null, $guru->jenis_kelamin);
        });

        return redirect()->route('admin.guru.index')
                         ->with('success','Guru berhasil ditambahkan.');
    }

    // Tampilkan detail guru
    public function show(int $id)
    {
        $guru = Guru::with('unit:id,nama_unit')->findOrFail($id);
        return view('admin.guru.show', compact('guru'));
    }

    // Tampilkan form edit
    public function edit(Request $request, int $id)
    {
        $user = $request->user();
        $guru = Guru::findOrFail($id);

        if ($this->isUnitScoped($user) && (int)$guru->unit_id !== (int)$user->unit_id) {
            abort(403, 'Anda hanya dapat mengedit guru di unit Anda.');
        }

        if ($this->isUnitScoped($user)) {
            $units = Unit::where('id', $user->unit_id)
                         ->get(['id','nama_unit']);
        } else {
            $units = Unit::orderBy('id')
                         ->get(['id','nama_unit']);
        }

        $userGuru = User::where('linked_guru_id', $guru->id)->first();
        $jabatanSelected = '';
        if ($userGuru && method_exists($userGuru, 'hasRole')) {
            if ($userGuru->hasRole('koordinator_tahfizh_putra')) {
                $jabatanSelected = 'koordinator_tahfizh_putra';
            } elseif ($userGuru->hasRole('koordinator_tahfizh_putri')) {
                $jabatanSelected = 'koordinator_tahfizh_putri';
            } elseif ($userGuru->hasRole('wali_kelas')) {
                $jabatanSelected = 'wali_kelas';
            }
        }

        return view('admin.guru.edit', compact('guru','units','jabatanSelected'));
    }

    // Update guru
    public function update(Request $request, int $id)
    {
        $userLogin = $request->user();
        $guru = Guru::findOrFail($id);

        if ($this->isUnitScoped($userLogin) && (int)$guru->unit_id !== (int)$userLogin->unit_id) {
            abort(403, 'Anda hanya dapat mengedit guru di unit Anda.');
        }

        $data = $request->validate([
            'nama'          => ['required','string','max:150'],
            'nip'           => ['nullable','string','max:50', Rule::unique('guru','nip')->ignore($guru->id)],
            'jenis_kelamin' => ['required', Rule::in(['L','P'])],
            'unit_id'       => [$this->isUnitScoped($userLogin) ? 'required' : 'nullable','integer','exists:units,id'],
            'status_aktif'  => ['required', Rule::in(['aktif','nonaktif'])],
            'jabatan'       => ['nullable', Rule::in(self::JABATAN_LIST())],
        ]);

        if ($this->isUnitScoped($userLogin)) {
            $data['unit_id'] = (int)$userLogin->unit_id;
        }

        DB::transaction(function () use ($guru, $data) {
            $guru->update([
                'nama'          => $data['nama'],
                'nip'           => $data['nip'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'unit_id'       => $data['unit_id'],
                'status_aktif'  => $data['status_aktif'],
            ]);

            $user = User::where('linked_guru_id', $guru->id)->first();
            if (!$user) {
                $user = $this->createUserForGuru($guru);
            } else {
                $user->update([
                    'name'    => $guru->nama,
                    'unit_id' => $guru->unit_id,
                ]);
            }

            $this->syncJabatanRole($user, $data['jabatan'] ?? null, $guru->jenis_kelamin);
        });

        return redirect()->route('admin.guru.index')
                         ->with('success', 'Data guru diperbarui.');
    }

    // Hapus guru + user terkait
    public function destroy(int $id)
    {
        $guru = Guru::findOrFail($id);

        DB::transaction(function () use ($guru) {
            $user = User::where('linked_guru_id', $guru->id)->first();

            if ($user) {
                if (method_exists($user, 'roles') && $user->roles()->count() > 0) {
                    foreach ($user->getRoleNames() as $role) {
                        $user->removeRole($role);
                    }
                }
                $user->delete();
            }

            $guru->delete();
        });

        return back()->with('success', 'Data guru dan akun pengguna terkait telah dihapus.');
    }

    // Membuat akun user otomatis untuk guru baru
    private function createUserForGuru(Guru $guru): ?User
    {
        $existing = User::where('linked_guru_id', $guru->id)->first();
        if ($existing) {
            if (!preg_match('/^[a-z]+[0-9]{2}$/', $existing->username)) {
                $newUsername = $this->generateUsername2Digits($guru->nama);
                if (!User::where('username', $newUsername)->where('id','!=',$existing->id)->exists()) {
                    $existing->username = $newUsername;
                    $existing->email    = $newUsername.'@yayasan.local';
                    $existing->save();
                }
            }
            return $existing;
        }

        $username = $this->generateUsername2Digits($guru->nama);
        $email    = $username . '@yayasan.local';

        $user = User::withoutEvents(function () use ($guru, $username, $email) {
            return User::create([
                'name'           => $guru->nama,
                'email'          => $email,
                'username'       => $username,
                'password'       => bcrypt('password'),
                'role'           => 'guru',
                'unit_id'        => $guru->unit_id,
                'linked_guru_id' => $guru->id,
            ]);
        });

        if (method_exists($user, 'assignRole') && !$user->hasRole('guru')) {
            $user->assignRole('guru');
        }

        return $user;
    }

    private function generateUsername2Digits(string $nama): string
    {
        $first = trim(preg_split('/\s+/', $nama)[0] ?? 'user');
        $base  = strtolower(preg_replace('/[^a-z]/i', '', $first)) ?: 'user';

        // Coba maksimal 50 kali dengan angka acak
        for ($i = 0; $i < 50; $i++) {
            $rand = str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
            $candidate = $base . $rand;
            if (!User::where('username', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Fallback: pakai angka urut jika semua angka acak sudah terpakai
        $existing = User::where('username','like',$base.'%')->pluck('username')->all();
        $used = [];
        foreach ($existing as $u) {
            if (preg_match('/^'.preg_quote($base, '/').'([0-9]{2})$/', $u, $m)) {
                $used[(int)$m[1]] = true;
            }
        }

        for ($i = 1; $i <= 99; $i++) {
            if (!isset($used[$i])) {
                return $base . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            }
        }

        // Fallback terakhir
        return $base . str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
    }

    private function syncJabatanRole(User $user, ?string $jabatan, ?string $jenisKelamin)
    {
        if (!$user) return;

        $jabatanLower = strtolower($jabatan ?? 'guru');
        if (!in_array($jabatanLower, self::JABATAN_LIST(), true)) {
            $jabatanLower = 'guru';
        }

        $user->update(['role' => $jabatanLower]);

        $validRoles = self::JABATAN_LIST();
        if (method_exists($user, 'hasAnyRole') && method_exists($user, 'syncRoles')) {
            if ($user->hasAnyRole($validRoles)) {
                $user->syncRoles([$jabatanLower]);
            } else {
                $user->assignRole($jabatanLower);
            }
        }
    }

    // Helper: cek apakah user terbatas ke unit
    private function isUnitScoped($user): bool
    {
        return in_array(strtolower($user->role ?? ''), ['admin','operator'], true)
            && !empty($user->unit_id);
    }

    // Daftar konstanta jabatan
    private static function JABATAN_LIST(): array
    {
        return ['guru','wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri'];
    }
}
