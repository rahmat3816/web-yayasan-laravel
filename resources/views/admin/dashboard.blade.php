@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
@php
    // Guard variabel agar aman
    $isSuperadmin = $isSuperadmin ?? ( (isset($authUser) && method_exists($authUser,'hasRole')) ? $authUser->hasRole('superadmin') : false );
    $unitScopeId  = $unitScopeId  ?? null;

    // Statistik: dukung dua skema nama variabel agar kompatibel
    $valSantri  = $totalSantri  ?? ($santriCount  ?? ($stats['totalSantri']  ?? 0));
    $valGuru    = $totalGuru    ?? ($guruCount    ?? ($stats['totalGuru']    ?? 0));
    $valHalaqoh = $totalHalaqoh ?? ($halaqohCount ?? ($stats['totalHalaqoh'] ?? 0));
    $valUnit    = $totalUnit    ?? ($unitCount    ?? ($stats['totalUnits']   ?? 0));

    /** @var \Illuminate\Pagination\LengthAwarePaginator|null $users */
    $users   = $users   ?? null;
    $search  = $search  ?? request('q', '');
    $unitMap = collect($unitMap ?? []);
@endphp

<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-2">
                <span>📚</span> <span>Dashboard Admin</span>
            </h1>
            @if($unitScopeId)
                <p class="text-sm text-gray-500 mt-1">Menampilkan data untuk <b>Unit ID {{ $unitScopeId }}</b>.</p>
            @else
                <p class="text-sm text-gray-500 mt-1">Menampilkan ringkasan seluruh unit {{ $isSuperadmin ? '(Superadmin)' : '' }}.</p>
            @endif
        </div>
        <a href="{{ route('admin.laporan.index') }}"
           class="px-4 py-2 rounded-xl font-medium shadow hover:shadow-md transition
                  bg-gradient-to-r from-indigo-500 to-blue-600 text-white">
            Laporan
        </a>
    </div>

    {{-- KARTU STAT WARNA-WARNI --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Santri --}}
        <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg
                    bg-gradient-to-br from-emerald-400 via-emerald-500 to-emerald-600 text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 bg-white"></div>
            <div class="text-sm/5 opacity-90">Santri</div>
            <div class="mt-1 text-4xl font-extrabold">{{ number_format($valSantri) }}</div>
            <div class="mt-2 text-xs/5 opacity-90">Total santri terdata</div>
        </div>

        {{-- Guru --}}
        <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg
                    bg-gradient-to-br from-fuchsia-500 via-pink-500 to-rose-500 text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 bg-white"></div>
            <div class="text-sm/5 opacity-90">Guru</div>
            <div class="mt-1 text-4xl font-extrabold">{{ number_format($valGuru) }}</div>
            <div class="mt-2 text-xs/5 opacity-90">Total guru aktif</div>
        </div>

        {{-- Halaqoh --}}
        <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg
                    bg-gradient-to-br from-sky-500 via-cyan-500 to-teal-500 text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 bg-white"></div>
            <div class="text-sm/5 opacity-90">Halaqoh</div>
            <div class="mt-1 text-4xl font-extrabold">{{ number_format($valHalaqoh) }}</div>
            <div class="mt-2 text-xs/5 opacity-90">Kelompok halaqoh aktif</div>
        </div>

        {{-- Unit --}}
        <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg
                    bg-gradient-to-br from-amber-400 via-orange-500 to-red-500 text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 bg-white"></div>
            <div class="text-sm/5 opacity-90">Unit Pendidikan</div>
            <div class="mt-1 text-4xl font-extrabold">{{ number_format($valUnit) }}</div>
            <div class="mt-2 text-xs/5 opacity-90">Total unit terdaftar</div>
        </div>
    </div>

    {{-- DAFTAR UNIT (opsional, tampil untuk superadmin) --}}
    @if(!$unitScopeId && isset($units) && $units->count())
        <div class="rounded-2xl border shadow-sm overflow-hidden bg-white dark:bg-gray-800">
            <div class="px-5 py-3 border-b dark:border-gray-700 font-semibold flex items-center gap-2">
                <span>🏫</span><span>Daftar Unit</span>
            </div>
            <div class="p-5">
                <div class="flex flex-wrap gap-2">
                    @foreach($units as $u)
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                     bg-gradient-to-r from-slate-100 to-slate-200 dark:from-gray-900 dark:to-gray-800
                                     border border-slate-200 dark:border-gray-700">
                            {{ $u->nama_unit }} <span class="text-gray-500">(#{{ $u->id }})</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- QUICK LINKS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.santri.index') }}"
           class="group block rounded-2xl p-5 border shadow-sm hover:shadow-md transition
                  bg-white dark:bg-gray-800 hover:border-emerald-300">
            <div class="text-lg font-semibold flex items-center gap-2">
                <span>👨‍🎓</span><span>Kelola Santri</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Tambah, ubah, atau kelola data santri.</p>
        </a>

        <a href="{{ route('admin.guru.index') }}"
           class="group block rounded-2xl p-5 border shadow-sm hover:shadow-md transition
                  bg-white dark:bg-gray-800 hover:border-fuchsia-300">
            <div class="text-lg font-semibold flex items-center gap-2">
                <span>👩‍🏫</span><span>Kelola Guru</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Data guru & penugasan jabatan.</p>
        </a>

        <a href="{{ route('admin.halaqoh.index') }}"
           class="group block rounded-2xl p-5 border shadow-sm hover:shadow-md transition
                  bg-white dark:bg-gray-800 hover:border-sky-300">
            <div class="text-lg font-semibold flex items-center gap-2">
                <span>📖</span><span>Kelola Halaqoh</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Buat, ubah pengampu, tambah santri.</p>
        </a>
    </div>

    {{-- DATA USER — KHUSUS SUPERADMIN --}}
    @if($isSuperadmin)
        <div class="rounded-2xl border shadow-sm overflow-hidden bg-white dark:bg-gray-800">
            <div class="px-5 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                <div class="font-semibold flex items-center gap-2">
                    <span>👥</span><span>Data User</span>
                </div>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $search }}"
                           placeholder="Cari nama / email / username…"
                           class="w-64 md:w-80 border rounded-lg px-3 py-2 dark:bg-gray-900 dark:border-gray-700" />
                    <button class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Cari</button>
                    @if($search !== '')
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg border">Reset</a>
                    @endif
                </form>
            </div>

            <div class="p-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-300 border-b dark:border-gray-700">
                            <th class="py-2 pr-4">#</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Username</th>
                            <th class="py-2 pr-4">Email</th>
                            <th class="py-2 pr-4">Role</th>
                            <th class="py-2 pr-4">Unit</th>
                            <th class="py-2 pr-4">Linked</th>
                            <th class="py-2 pr-4">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @forelse(($users?->items() ?? []) as $i => $u)
                            @php
                                $roleNames = method_exists($u, 'getRoleNames') ? $u->getRoleNames()->implode(', ') : ($u->role ?? '—');
                                $unitName  = $unitMap[$u->unit_id] ?? '—';
                                $linked    = [];
                                if (!empty($u->linked_guru_id))   { $linked[] = 'Guru#'.$u->linked_guru_id; }
                                if (!empty($u->linked_santri_id)) { $linked[] = 'Santri#'.$u->linked_santri_id; }
                                $linkedTxt = $linked ? implode(', ', $linked) : '—';
                            @endphp
                            <tr class="align-top">
                                <td class="py-2 pr-4">{{ ($users->firstItem() ?? 1) + $i }}</td>
                                <td class="py-2 pr-4 font-medium">{{ $u->name }}</td>
                                <td class="py-2 pr-4">{{ $u->username ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ $u->email }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                        {{ $roleNames }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">{{ $unitName }}</td>
                                <td class="py-2 pr-4">{{ $linkedTxt }}</td>
                                <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada user yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users)
                <div class="px-5 pb-5">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
