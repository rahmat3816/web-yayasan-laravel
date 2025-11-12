@extends('layouts.admin')
@section('title', 'Edit Santri')

@section('content')
<x-breadcrumb label="Edit Santri" />

<section class="glass-card max-w-4xl mx-auto p-8 space-y-6">
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Pembaruan Data</p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-white">Edit Data Santri</h1>
            <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">
                Perubahan akan terhubung ke akun wali santri serta progres hafalan secara otomatis.
            </p>
        </div>
        <a href="{{ route('admin.santri.index') }}" class="btn btn-sm btn-outline rounded-full">← Kembali</a>
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

    <form action="{{ route('admin.santri.update', $santri->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-5">
            <label class="form-control">
                <span class="label-text">NISY (Yayasan)</span>
                <input type="text" value="{{ $santri->nisy }}" class="input input-bordered bg-base-200" readonly>
            </label>

            <label class="form-control">
                <span class="label-text">Nama Santri</span>
                <input type="text" name="nama" value="{{ old('nama', $santri->nama) }}" class="input input-bordered" required>
            </label>

            <label class="form-control">
                <span class="label-text">NISN</span>
                <input type="text" name="nisn" value="{{ old('nisn', $santri->nisn) }}" class="input input-bordered">
            </label>

            <label class="form-control">
                <span class="label-text">Jenis Kelamin</span>
                <select name="jenis_kelamin" class="select select-bordered" required>
                    <option value="L" @selected(old('jenis_kelamin', $santri->jenis_kelamin)==='L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $santri->jenis_kelamin)==='P')>Perempuan</option>
                </select>
            </label>

            @role('superadmin')
                <label class="form-control">
                    <span class="label-text">Unit Pendidikan</span>
                    <select name="unit_id" class="select select-bordered" required>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $santri->unit_id)==$unit->id)>{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </label>
            @endrole

            <label class="form-control">
                <span class="label-text">Tahun Masuk</span>
                <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk', $santri->tahun_masuk) }}" class="input input-bordered">
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-warning rounded-full px-6">Simpan Perubahan</button>
        </div>
    </form>
</section>
@endsection
