{{-- ==============================
📘 Detail Guru – View Only
============================== --}}
@extends('layouts.admin')

@section('title', 'Detail Guru')

@section('content')
    <x-breadcrumb title="Detail Guru" />

    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">👨‍🏫 Detail Guru</h1>

        {{-- 🔁 Kembali ke Data Guru --}}
        <div class="mb-4">
            <a href="{{ route('admin.guru.index') }}" class="text-blue-600 hover:underline">
                ← Kembali ke Data Guru
            </a>
        </div>

        <div class="space-y-3 text-gray-700 dark:text-gray-200">
            <div>
                <span class="font-semibold">Nama:</span>
                <p>{{ $guru->nama }}</p>
            </div>

            <div>
                <span class="font-semibold">NIP / NIK:</span>
                <p>{{ $guru->nip ?? '-' }}</p>
            </div>

            <div>
                <span class="font-semibold">Jenis Kelamin:</span>
                <p>{{ $guru->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>

            <div>
                <span class="font-semibold">Unit:</span>
                <p>{{ $guru->unit->nama_unit ?? '-' }}</p>
            </div>

            <div>
                <span class="font-semibold">Status Aktif:</span>
                <p class="{{ $guru->status_aktif === 'aktif' ? 'text-green-600' : 'text-red-600' }}">
                    {{ ucfirst($guru->status_aktif) }}
                </p>
            </div>

            <div>
                <span class="font-semibold">Tanggal Input:</span>
                <p>{{ $guru->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('admin.guru.edit', $guru->id) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">
                ✏️ Edit Data
            </a>
        </div>
    </div>
@endsection
