{{-- ==============================
📘 Edit Santri – Admin & Operator
============================== --}}
@extends('layouts.admin')

@section('title', 'Edit Santri')

@section('content')
<x-breadcrumb title="Edit Santri" />

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">✏️ Edit Data Santri</h1>

    {{-- 🔁 Kembali ke Data Santri --}}
    <div class="mb-4">
        <a href="{{ route('admin.santri.index') }}" class="text-blue-600 hover:underline">
            ← Kembali ke Data Santri
        </a>
    </div>

    {{-- ⚠️ Pesan Error Validasi --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4 dark:bg-red-900 dark:text-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 📝 Form Edit --}}
    <form action="{{ route('admin.santri.update', $santri->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- NISY (readonly, otomatis) --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">NISY (Yayasan)</label>
            <input type="text" name="nisy" value="{{ $santri->nisy }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded px-3 py-2 bg-gray-100 dark:bg-gray-700" readonly>
        </div>

        {{-- Nama Santri --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Santri</label>
            <input type="text" name="nama" value="{{ old('nama', $santri->nama) }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 focus:ring focus:ring-blue-300" required>
        </div>

        {{-- NISN (nasional) --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">NISN (Nasional)</label>
            <input type="text" name="nisn" value="{{ old('nisn', $santri->nisn) }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2">
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Jenis Kelamin</label>
            <select name="jenis_kelamin"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ old('jenis_kelamin', $santri->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $santri->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        {{-- Unit hanya tampil untuk superadmin --}}
        @role('superadmin')
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Unit Pendidikan</label>
            <select name="unit_id"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $santri->unit_id) == $unit->id ? 'selected' : '' }}>
                        {{ $unit->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>
        @endrole

        {{-- Tahun Masuk --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Tahun Masuk</label>
            <input type="number" name="tahun_masuk" value="{{ old('tahun_masuk', $santri->tahun_masuk) }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2">
        </div>

        {{-- Tombol Simpan --}}
        <div class="pt-4">
            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
