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

            // 🔹 Buat akun user otomatis (cek duplikasi)
            $user = $this->createUserForGuru($guru);

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

        DB::transaction(function () use ($guru, $data) {
            $guru->update([
                'nama'          => $data['nama'],
                'nip'           => $data['nip'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'unit_id'       => $data['unit_id'],
                'status_aktif'  => $data['status_aktif'],
            ]);

            // Pastikan hanya 1 user terhubung
            $user = User::where('linked_guru_id', $guru->id)->first();
            if (!$user) {
                $user = $this->createUserForGuru($guru);
            } else {
                $user->update([
                    'name' => $guru->nama,
                    'unit_id' => $guru->unit_id,
                ]);
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

            if ($user) {
                // 🔹 Hapus semua role dari user
                if (method_exists($user, 'roles') && $user->roles()->count() > 0) {
                    foreach ($user->getRoleNames() as $role) {
                        $user->removeRole($role);
                    }
                }

                // 🔹 Hapus akun user terkait guru
                $user->delete();
            }

            // 🔹 Hapus data guru
            $guru->delete();
        });

        return back()->with('success', 'Data guru dan akun pengguna terkait telah dihapus.');
    }

    /**
     * 🔹 Membuat akun user otomatis untuk guru baru
     * Username otomatis = nama depan + 2 digit angka unik (contoh: fani23)
     */
    private function createUserForGuru(Guru $guru): ?User
    {
        // 🔒 Kalau sudah ada user terhubung, koreksi bila formatnya salah
        $existing = User::where('linked_guru_id', $guru->id)->first();
        if ($existing) {
            // Jika username bukan pola "huruf + 2 digit", perbaiki sekarang
            if (!preg_match('/^[a-z]+[0-9]{2}$/', $existing->username)) {
                $new = $this->generateUsername2Digits($guru->nama);
                // Pastikan unik terhadap user lain
                if (!User::where('username', $new)->where('id', '!=', $existing->id)->exists()) {
                    $existing->username = $new;
                    $existing->email    = $new.'@yayasan.local';
                    $existing->save();
                }
            }
            return $existing;
        }

        // 🔹 Bangun username sesuai aturan
        $username = $this->generateUsername2Digits($guru->nama);
        $email    = $username . '@yayasan.local';

        // ❗ Bypass event "creating" di model User yg mungkin menimpa username
        $user = \App\Models\User::withoutEvents(function () use ($guru, $username, $email) {
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

        // Role default
        if (method_exists($user, 'assignRole') && !$user->hasRole('guru')) {
            $user->assignRole('guru');
        }

        return $user;
    }

    /**
     * Contoh:
     *  "Fani Eldiana"   -> "fani23"
     *  "Mushowwir Umar" -> "mushowwir33"
     * (huruf kecil + 2 digit, unik di DB)
     */
    private function generateUsername2Digits(string $nama): string
    {
        // Ambil kata pertama dari nama, buang non-alfabet
        $first = trim(preg_split('/\s+/', $nama)[0] ?? 'user');
        $base  = strtolower(preg_replace('/[^a-z]/i', '', $first)) ?: 'user';

        // Ambil semua username yg punya prefix sama (untuk menghindari tabrakan)
        $existing = User::where('username', 'like', $base.'%')->pluck('username')->all();
        $used = [];
        foreach ($existing as $u) {
            if (preg_match('/^'.preg_quote($base, '/').'([0-9]{2})$/', $u, $m)) {
                $used[(int) $m[1]] = true;
            }
        }

        // Cari 2 digit yg belum dipakai
        for ($i = 1; $i <= 99; $i++) {
            if (!isset($used[$i])) {
                $candidate = $base . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                if (!User::where('username', $candidate)->exists()) {
                    return $candidate;
                }
            }
        }

        // Fallback terakhir (harusnya tidak pernah sampai sini)
        return $base . str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
    }


    private function syncJabatanRole($user, ?string $jabatan, ?string $jenisKelamin)
    {
        if (!$user) return;

        $validRoles = ['guru','wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri'];
        $jabatan = strtolower($jabatan ?? 'guru');
        if (!in_array($jabatan, $validRoles, true)) $jabatan = 'guru';

        $user->update(['role' => $jabatan]);
        if ($user->hasAnyRole($validRoles)) {
            $user->syncRoles([$jabatan]);
        } else {
            $user->assignRole($jabatan);
        }
    }
}
