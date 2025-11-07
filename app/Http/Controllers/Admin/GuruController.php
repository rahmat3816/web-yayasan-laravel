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

    /* ======================================================
     * 🔧 Helpers
     * ====================================================== */

     private function createUserForGuru(Guru $guru): ?User
     {
         // 🔒 Cegah duplikasi user
         $existing = User::where('linked_guru_id', $guru->id)->first();
         if ($existing) {
             return $existing;
         }

         // 🔹 Gunakan nama suku kata pertama + 2 digit unik
         $username = $this->generateUsername2Digits($guru->nama);
         $email    = $username . '@yayasan.local';

         // 🔹 Buat user baru (forceFill agar tidak dipengaruhi mutator model)
         $user = new User();
         $user->forceFill([
             'name'           => $guru->nama,
             'email'          => $email,
             'username'       => $username,
             'password'       => bcrypt('password'),
             'role'           => 'guru',
             'unit_id'        => $guru->unit_id,
             'linked_guru_id' => $guru->id,
         ])->save();

         // 🔹 Hardening: pastikan format username tetap “kata + 2 digit”
         if (!preg_match('/^[a-z0-9]+[0-9]{2}$/', $user->username)) {
             $user->username = $this->generateUsername2Digits($guru->nama);
             $user->email    = $user->username.'@yayasan.local';
             $user->save();
         }

         // 🔹 Pastikan punya role 'guru' default
         if (method_exists($user, 'assignRole') && !$user->hasRole('guru')) {
             $user->assignRole('guru');
         }

         return $user;
     }

    private function generateUsername2Digits(string $nama): string
    {
        // Ambil hanya kata pertama dari nama
        $nama = trim($nama) ?: 'user';
        $first = trim(preg_split('/\\s+/', $nama)[0] ?? '');
        $base = strtolower(preg_replace('/[^a-z]/', '', $first)) ?: 'user';

        // Cek username yang sudah ada dengan prefix yang sama
        $existing = User::where('username', 'like', $base.'%')->pluck('username')->toArray();

        // Cari angka acak 2 digit yang belum terpakai
        for ($i = 0; $i < 30; $i++) {
            $random = str_pad((string) random_int(1, 99), 2, '0', STR_PAD_LEFT);
            $username = $base . $random;
            if (!in_array($username, $existing)) {
                return $username;
            }
        }

        // fallback terakhir (jarang terjadi)
        return $base . str_pad((string) random_int(1, 99), 2, '0', STR_PAD_LEFT);
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
