<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruRoleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $guru = Guru::with([
                'user.jabatans' => fn ($query) => $query->orderBy('nama_jabatan'),
                'unit:id,nama_unit',
            ])
            ->when($q, fn ($qq) => $qq->where('nama', 'like', '%' . $q . '%'))
            ->orderBy('nama')
            ->paginate(20);

        return view('admin.guru.jabatan.index', [
            'guru' => $guru,
            'search' => $q,
        ]);
    }

    public function edit(Request $request, int $guruId)
    {
        $guru = Guru::with(['user.jabatans', 'unit'])->findOrFail($guruId);
        $actor = $request->user();
        $assignable = $this->assignableJabatanQuery($actor)->get();

        if ($assignable->isEmpty()) {
            abort(403, 'Anda tidak memiliki wewenang untuk menetapkan jabatan.');
        }

        $current = $guru->user?->jabatans->pluck('id')->all() ?? [];

        return view('admin.guru.jabatan.edit', [
            'guru' => $guru,
            'assignableJabatan' => $assignable,
            'currentAssignments' => $current,
        ]);
    }

    public function update(Request $request, int $guruId)
    {
        $guru = Guru::with(['user', 'unit'])->findOrFail($guruId);
        $actor = $request->user();

        $selected = collect($request->input('jabatan_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $assignable = $this->assignableJabatanQuery($actor)
            ->whereIn('jabatan.id', $selected->all())
            ->get()
            ->keyBy('id');

        if ($selected->count() && $assignable->count() !== $selected->count()) {
            throw ValidationException::withMessages([
                'jabatan_ids' => 'Anda mencoba memilih jabatan di luar kewenangan.',
            ]);
        }

        DB::transaction(function () use ($assignable, $guru) {
            $user = $guru->user ?: $this->ensureUserForGuru($guru);
            $syncPayload = [];

            foreach ($assignable as $jabatan) {
                $unitId = $this->resolveUnitForJabatan($jabatan, $guru);
                if (!$unitId) {
                    throw ValidationException::withMessages([
                        'jabatan_ids' => "Jabatan {$jabatan->nama_jabatan} hanya berlaku untuk unit pondok.",
                    ]);
                }

                if ($this->isJabatanOccupied($jabatan->id, $unitId, $user->id)) {
                    throw ValidationException::withMessages([
                        'jabatan_ids' => "Jabatan {$jabatan->nama_jabatan} di unit ini sudah terisi.",
                    ]);
                }

                $syncPayload[$jabatan->id] = ['unit_id' => $unitId];
            }

            $user->jabatans()->sync($syncPayload);
        });

        return redirect()
            ->route('admin.guru.jabatan.index')
            ->with('success', 'Penugasan jabatan guru diperbarui.');
    }

    protected function assignableJabatanQuery(?User $actor)
    {
        $slugs = $this->collectAssignableSlugs($actor);

        return Jabatan::query()
            ->select('jabatan.*')
            ->when($slugs->isNotEmpty(), fn ($q) => $q->whereIn('slug', $slugs->all()))
            ->orderBy('nama_jabatan');
    }

    protected function collectAssignableSlugs(?User $actor): Collection
    {
        if (!$actor) {
            return collect();
        }

        if ($actor->isSuperadmin()) {
            return collect(array_keys(config('jabatan.roles', [])));
        }

        $available = collect(config('jabatan.roles', []))
            ->filter(function ($meta, $slug) use ($actor) {
                $assignableBy = $meta['assignable_by'] ?? ['superadmin'];
                foreach ($assignableBy as $roleSlug) {
                    if ($actor->hasRole($roleSlug) || $actor->hasJabatan($roleSlug)) {
                        return true;
                    }
                }
                return false;
            })
            ->keys();

        // Admin lama (legacy)
        if (strtolower($actor->role ?? '') === 'admin' && $actor->unit_id) {
            return $available->merge(array_keys(config('jabatan.roles', [])))->unique();
        }

        return $available->unique();
    }

    protected function resolveUnitForJabatan(Jabatan $jabatan, Guru $guru): ?int
    {
        $scope = config("jabatan.roles.{$jabatan->slug}.scope", 'unit');

        if ($scope === 'pondok') {
            $allowedNames = collect(config('jabatan.units.pondok_group', []))->map(fn ($name) => strtolower($name));
            $unitName = strtolower($guru->unit?->nama_unit ?? '');
            if (!$allowedNames->contains($unitName)) {
                return null;
            }
        }

        return $guru->unit_id;
    }

    protected function isJabatanOccupied(int $jabatanId, ?int $unitId, int $userId): bool
    {
        if (!$unitId) {
            return false;
        }

        return DB::table('guru_jabatan')
            ->where('jabatan_id', $jabatanId)
            ->where('unit_id', $unitId)
            ->where('user_id', '!=', $userId)
            ->exists();
    }

    protected function ensureUserForGuru(Guru $guru): User
    {
        $user = $guru->user;
        if ($user) {
            return $user;
        }

        $username = \Str::slug($guru->nama, '') ?: 'guru';
        $base = $username;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        return User::create([
            'name' => $guru->nama,
            'email' => $username . '@yayasan.local',
            'username' => $username,
            'password' => \Hash::make('password'),
            'role' => 'guru',
            'unit_id' => $guru->unit_id,
            'linked_guru_id' => $guru->id,
        ]);
    }
}
