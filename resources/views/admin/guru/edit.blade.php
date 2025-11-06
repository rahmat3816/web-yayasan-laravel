{{-- ==============================
📘 Edit Data Guru – Admin & Operator
============================== --}}
@extends('layouts.admin')
@section('title', 'Edit Guru')

@section('content')
<x-breadcrumb title="Edit Guru" />

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">✏️ Edit Data Guru</h1>

    <div class="mb-4">
        <a href="{{ route('admin.guru.index') }}" class="text-blue-600 hover:underline">← Kembali ke Data Guru</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4 dark:bg-red-900 dark:text-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.guru.update', $guru->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Nama Guru --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Guru</label>
            <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 focus:ring focus:ring-blue-300"
                   required>
        </div>

        {{-- NIP / NIK --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">NIP / NIK</label>
            <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 focus:ring focus:ring-blue-300">
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Jenis Kelamin</label>
            <select name="jenis_kelamin"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" @selected(old('jenis_kelamin', $guru->jenis_kelamin)==='L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin', $guru->jenis_kelamin)==='P')>Perempuan</option>
            </select>
        </div>

        {{-- Unit Pendidikan (superadmin) --}}
        @role('superadmin')
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Unit Pendidikan</label>
            <select name="unit_id"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id', $guru->unit_id)==$unit->id)>{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
        </div>
        @endrole

        {{-- Status Aktif --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Status Aktif</label>
            <select name="status_aktif"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="aktif" @selected(old('status_aktif', $guru->status_aktif)==='aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status_aktif', $guru->status_aktif)==='nonaktif')>Nonaktif</option>
            </select>
        </div>

        {{-- Jabatan --}}
        @php $sel = old('jabatan', $jabatanSelected ?? ''); @endphp
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Jabatan (opsional)</label>
            <select name="jabatan"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2">
                <option value="">— Pilih Jabatan —</option>
                <option value="wali_kelas" @selected($sel==='wali_kelas')>Wali Kelas</option>
                <option value="koordinator_tahfizh_putra" @selected($sel==='koordinator_tahfizh_putra')>Koordinator Tahfizh Putra</option>
                <option value="koordinator_tahfizh_putri" @selected($sel==='koordinator_tahfizh_putri')>Koordinator Tahfizh Putri</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                • Koordinator Putra/Putri harus sesuai jenis kelamin guru.<br>
                • Jika memilih koordinator, <b>sistem otomatis menggantikan</b> koordinator lama di unit ini.
            </p>
        </div>

        <div class="pt-4">
            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
