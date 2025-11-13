@extends('layouts.admin')
@section('title', 'Atur Jabatan Guru')

@section('content')
<x-breadcrumb label="Atur Jabatan" />

<section class="glass-card max-w-4xl mx-auto p-8 space-y-6">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Penugasan</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">{{ $guru->nama }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                Unit: <strong>{{ $guru->unit->nama_unit ?? '-' }}</strong>. Pilih jabatan yang relevan untuk guru ini.
            </p>
        </div>
        <a href="{{ route('admin.guru.jabatan.index') }}" class="btn btn-sm btn-outline rounded-full"><- Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error shadow-lg">
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.guru.jabatan.update', $guru->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            @foreach ($assignableJabatan as $jabatan)
                <label class="glass-card px-4 py-3 flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="jabatan_ids[]" value="{{ $jabatan->id }}"
                           @checked(in_array($jabatan->id, $currentAssignments, true))
                           class="checkbox checkbox-sm mt-1">
                    <div>
                        <p class="font-semibold">{{ $jabatan->nama_jabatan }}</p>
                        <p class="text-xs text-slate-500">
                            Scope: {{ ucfirst(config("jabatan.roles.{$jabatan->slug}.scope", 'unit')) }}
                        </p>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.guru.jabatan.index') }}" class="btn btn-sm btn-outline rounded-full">Batal</a>
            <button class="btn btn-primary rounded-full px-6">Simpan Penugasan</button>
        </div>
    </form>
</section>
@endsection
