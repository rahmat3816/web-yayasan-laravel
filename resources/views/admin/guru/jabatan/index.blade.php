@extends('layouts.admin')
@section('title', 'Penugasan Jabatan Guru')

@section('content')
<x-breadcrumb label="Penugasan Jabatan" />

<section class="glass-card p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Penugasan</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">Penugasan Jabatan Guru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                Satu jabatan hanya boleh ditempati satu guru. Gunakan form ini untuk memetakan tanggung jawab lintas unit.
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama guru..." class="input input-bordered input-sm w-56">
            <button class="btn btn-sm btn-primary">Cari</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Guru</th>
                    <th>Unit</th>
                    <th>Jabatan Aktif</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($guru as $g)
                    <tr>
                        <td class="font-semibold">{{ $g->nama }}</td>
                        <td>{{ $g->unit->nama_unit ?? '—' }}</td>
                        <td>
                            @if ($g->user?->jabatans->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($g->user->jabatans as $jabatan)
                                        <span class="badge badge-sm badge-outline">{{ $jabatan->nama_jabatan }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400">Belum ada</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.guru.jabatan.edit', $g->id) }}" class="btn btn-xs btn-outline btn-primary">
                                Atur Jabatan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-slate-400">Tidak ada data guru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $guru->withQueryString()->links() }}
    </div>
</section>
@endsection
