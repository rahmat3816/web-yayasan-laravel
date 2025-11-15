@extends('layouts.admin')
@section('title', 'Edit Guru')

@section('content')
<x-breadcrumb label="Edit Guru" />

<section class="glass-card max-w-4xl mx-auto p-8 space-y-6">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Pembaruan Data</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">Edit Data Guru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                Perubahan akan tersinkron otomatis dengan penugasan jabatan dan dashboard guru terkait.
            </p>
        </div>
        <a href="{{ route('admin.guru.index') }}" class="btn btn-sm btn-outline rounded-full"><- Kembali</a>
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

    <form action="{{ route('admin.guru.update', $guru->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-5">
            <label class="form-control">
                <span class="label-text">Nama Guru</span>
                <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}" class="input input-bordered" required>
            </label>

            <label class="form-control">
                <span class="label-text">Jenis Kelamin</span>
                <select name="jenis_kelamin" class="select select-bordered" required>
                    <option value="L" @selected(old('jenis_kelamin', $guru->jenis_kelamin)==='L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $guru->jenis_kelamin)==='P')>Perempuan</option>
                </select>
            </label>

            @role('superadmin')
                <label class="form-control md:col-span-2">
                    <span class="label-text">Unit Pendidikan</span>
                    <select name="unit_id" class="select select-bordered" required>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $guru->unit_id)==$unit->id)>{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </label>
            @endrole

            <label class="form-control">
                <span class="label-text">Status Aktif</span>
                <select name="status_aktif" class="select select-bordered" required>
                    <option value="aktif" @selected(old('status_aktif', $guru->status_aktif)==='aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status_aktif', $guru->status_aktif)==='nonaktif')>Nonaktif</option>
                </select>
            </label>

            <label class="form-control">
                <span class="label-text">Tanggal Bergabung</span>
                <input type="date" name="tanggal_bergabung"
                       value="{{ old('tanggal_bergabung', optional($guru->tanggal_bergabung)->format('Y-m-d')) }}"
                       class="input input-bordered">
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.guru.show', $guru->id) }}" class="btn btn-sm btn-outline rounded-full">Lihat Detail</a>
            <button type="submit" class="btn btn-warning rounded-full px-6">Simpan Perubahan</button>
        </div>
    </form>
</section>
@endsection
