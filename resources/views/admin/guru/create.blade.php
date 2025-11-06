@extends('layouts.admin')
@section('title', 'Tambah Guru')

@section('content')
<x-breadcrumb title="Tambah Guru" />

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">➕ Tambah Guru</h1>

    <a href="{{ route('admin.guru.index') }}" class="text-blue-600 hover:underline">
        ← Kembali ke Data Guru
    </a>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mt-4 mb-4 dark:bg-red-900 dark:text-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.guru.store') }}" method="POST" class="space-y-4 mt-4">
        @csrf

        {{-- Nama Guru --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Guru</label>
            <input type="text" name="nama" value="{{ old('nama') }}"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100 focus:ring focus:ring-blue-300"
                required>
        </div>

        {{-- NIP / NIK --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">NIP / NIK</label>
            <input type="text" name="nip" value="{{ old('nip') }}"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100">
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Jenis Kelamin</label>
            <select name="jenis_kelamin"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100" required>
                <option value="">-- Pilih --</option>
                <option value="L" @selected(old('jenis_kelamin')==='L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin')==='P')>Perempuan</option>
            </select>
        </div>

        {{-- Unit Pendidikan (superadmin saja memilih; admin unit otomatis) --}}
        @role('superadmin')
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Unit Pendidikan</label>
            <select name="unit_id"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100" required>
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
        </div>
        @endrole

        {{-- Status Aktif --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Status Aktif</label>
            <select name="status_aktif"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100" required>
                <option value="">-- Pilih Status --</option>
                <option value="aktif" @selected(old('status_aktif')==='aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status_aktif')==='nonaktif')>Nonaktif</option>
            </select>
        </div>

        {{-- Jabatan --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Jabatan (opsional)</label>
            <select name="jabatan"
                class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:text-gray-100">
                <option value="">— Pilih Jabatan —</option>
                <option value="wali_kelas" @selected(old('jabatan')==='wali_kelas')>Wali Kelas</option>
                <option value="koordinator_tahfizh_putra" @selected(old('jabatan')==='koordinator_tahfizh_putra')>Koordinator Tahfizh Putra</option>
                <option value="koordinator_tahfizh_putri" @selected(old('jabatan')==='koordinator_tahfizh_putri')>Koordinator Tahfizh Putri</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                • Koordinator Putra/Putri harus sesuai jenis kelamin guru.<br>
                • Jika memilih koordinator, <b>sistem otomatis menggantikan</b> koordinator lama di unit ini.
            </p>
        </div>

        <div class="pt-4">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">
                💾 Simpan Data
            </button>
        </div>
    </form>
</div>
@endsection
