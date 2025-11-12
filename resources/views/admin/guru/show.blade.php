@extends('layouts.admin')
@section('title', 'Detail Guru')

@section('content')
<x-breadcrumb label="Detail Guru" />

<section class="glass-card max-w-4xl mx-auto p-8 space-y-6">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Profil Guru</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">{{ $guru->nama }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                NIPY: {{ $guru->nipy ?? 'Belum tersedia' }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.guru.index') }}" class="btn btn-sm btn-outline rounded-full">← Kembali</a>
            <a href="{{ route('admin.guru.edit', $guru->id) }}" class="btn btn-sm btn-warning rounded-full">Edit</a>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 text-sm">
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Jenis Kelamin</p>
            <p class="text-lg font-semibold">{{ $guru->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Unit Pendidikan</p>
            <p class="text-lg font-semibold">{{ $guru->unit->nama_unit ?? '—' }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Status</p>
            <p class="badge badge-lg {{ $guru->status_aktif === 'aktif' ? 'badge-success' : 'badge-ghost text-red-500' }}">
                {{ ucfirst($guru->status_aktif) }}
            </p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tanggal Bergabung</p>
            <p class="text-lg font-semibold">
                {{ optional($guru->tanggal_bergabung)->translatedFormat('d F Y') ?? '—' }}
            </p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tgl Input</p>
            <p class="text-lg font-semibold">{{ $guru->created_at->translatedFormat('d F Y H:i') }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tgl Pembaruan</p>
            <p class="text-lg font-semibold">{{ $guru->updated_at->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
</section>
@endsection
