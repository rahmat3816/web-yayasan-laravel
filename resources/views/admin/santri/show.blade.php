@extends('layouts.admin')
@section('title', 'Detail Santri')

@section('content')
<x-breadcrumb label="Detail Santri" />

<section class="glass-card max-w-4xl mx-auto p-8 space-y-6">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Profil Santri</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">{{ $santri->nama }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                NISN: {{ $santri->nisn ?? 'Belum tersedia' }} | NISY: {{ $santri->nisy }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.santri.index') }}" class="btn btn-sm btn-outline rounded-full"><- Kembali</a>
            @if (in_array(strtolower(auth()->user()->role), ['superadmin','admin','admin_unit','kepala_madrasah','wakamad_kurikulum','wakamad_kesiswaan','wakamad_sarpras','bendahara']))
                <a href="{{ route('admin.santri.edit', $santri->id) }}" class="btn btn-sm btn-warning rounded-full">Edit</a>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 text-sm">
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Jenis Kelamin</p>
            <p class="text-lg font-semibold">{{ $santri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Unit Pendidikan</p>
            <p class="text-lg font-semibold">{{ $santri->unit->nama_unit ?? '-' }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tahun Masuk</p>
            <p class="text-lg font-semibold">{{ $santri->tahun_masuk }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tanggal Input</p>
            <p class="text-lg font-semibold">{{ $santri->created_at->translatedFormat('d F Y H:i') }}</p>
        </div>
        <div class="glass-card p-4 md:col-span-2">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Tanggal Pembaruan</p>
            <p class="text-lg font-semibold">{{ $santri->updated_at->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
</section>
@endsection
