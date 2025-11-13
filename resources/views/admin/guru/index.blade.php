@extends('layouts.admin')
@section('title', 'Data Guru')

@section('content')
<x-breadcrumb label="Data Guru" />

@php
    $totalGuru = \App\Models\Guru::count();
    $aktif = \App\Models\Guru::where('status_aktif', 'aktif')->count();
    $nonaktif = $totalGuru - $aktif;
@endphp

<section class="hero-gradient text-white">
    <div class="relative z-10 grid gap-6 md:grid-cols-2">
        <div class="space-y-4">
            <span class="stat-badge">Direktori Guru Yayasan</span>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight">
                Pantau & perbarui data guru lintas unit
            </h1>
            <p class="text-sm md:text-base text-white/80">
                Riwayat NIPY, status keaktifan, dan unit penugasan terhubung otomatis dengan modul jabatan
                serta dashboard tahfizh. Gunakan panel ini untuk memastikan setiap guru memiliki akses dan peran yang tepat.
            </p>
            @if (in_array(strtolower(auth()->user()->role), ['superadmin', 'admin', 'admin_unit', 'kepala_madrasah', 'wakamad_kurikulum', 'wakamad_kesiswaan', 'wakamad_sarpras', 'bendahara']))
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.guru.create') }}" class="btn btn-sm btn-primary rounded-full shadow-lg shadow-blue-500/40">
                        + Tambah Guru
                    </a>
                    <a href="{{ route('admin.guru.jabatan.index') }}" class="btn btn-sm btn-outline rounded-full text-white border-white/60">
                        Kelola Jabatan
                    </a>
                </div>
            @endif
        </div>
        <div class="glass-card bg-white/20 dark:bg-slate-900/40 border border-white/30 dark:border-slate-800/60 p-6">
            <div class="grid grid-cols-3 gap-5 text-center">
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Total Guru</p>
                    <p class="text-3xl font-semibold">{{ number_format($totalGuru) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Aktif</p>
                    <p class="text-3xl font-semibold">{{ number_format($aktif) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Nonaktif</p>
                    <p class="text-3xl font-semibold">{{ number_format($nonaktif) }}</p>
                </div>
            </div>
        </div>
    </section>

@if (session('success'))
    <div class="alert alert-success mt-6 shadow-md">
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="glass-card mt-6 overflow-x-auto">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>NIPY</th>
                <th>Jenis Kelamin</th>
                <th>Unit</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($guru as $g)
                @php
                    $rowNumber = method_exists($guru, 'firstItem')
                        ? ($guru->firstItem() ?? 0) + $loop->index
                        : $loop->iteration;
                @endphp
                <tr>
                    <td>{{ $rowNumber }}</td>
                    <td class="font-semibold">{{ $g->nama }}</td>
                    <td>{{ $g->nipy ?? '-' }}</td>
                    <td>
                        <span class="badge badge-sm {{ $g->jenis_kelamin === 'L' ? 'badge-primary' : 'badge-secondary' }}">
                            {{ $g->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </td>
                    <td>{{ $g->unit->nama_unit ?? '-' }}</td>
                    <td>
                        <span class="badge badge-sm {{ $g->status_aktif === 'aktif' ? 'badge-success' : 'badge-ghost text-red-500' }}">
                            {{ ucfirst($g->status_aktif) }}
                        </span>
                    </td>
                    <td>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.guru.show', $g->id) }}" class="btn btn-xs btn-outline btn-info">Detail</a>
                            @if (in_array(strtolower(auth()->user()->role), ['superadmin', 'admin', 'admin_unit', 'kepala_madrasah', 'wakamad_kurikulum', 'wakamad_kesiswaan', 'wakamad_sarpras', 'bendahara']))
                                <a href="{{ route('admin.guru.edit', $g->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-400 py-6">Belum ada data guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $guru->links() }}
</div>
@endsection
