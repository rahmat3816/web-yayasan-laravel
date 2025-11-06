{{-- ==============================
📘 Detail Santri – Admin & Operator
============================== --}}
@extends('layouts.admin')

@section('title', 'Detail Santri')

@section('content')
<x-breadcrumb title="Detail Santri" />

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">👁️ Detail Data Santri</h1>

    <a href="{{ route('admin.santri.index') }}" class="text-blue-600 hover:underline mb-4 block">
        ← Kembali ke Data Santri
    </a>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-500 dark:text-gray-400">Nama Santri</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $santri->nama }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">NISN (Nasional)</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $santri->nisn ?? '-' }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">NISY (Yayasan)</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $santri->nisy }}</p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Jenis Kelamin</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">
                {{ $santri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
            </p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Unit Pendidikan</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">
                {{ $santri->unit->nama_unit ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-gray-500 dark:text-gray-400">Tahun Masuk</p>
            <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $santri->tahun_masuk }}</p>
        </div>

        <div class="sm:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Data terakhir diperbarui pada:</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                {{ $santri->updated_at->translatedFormat('d F Y, H:i') }}
            </p>
        </div>
    </div>
</div>
@endsection
