{{-- ==============================
📘 Detail Unit
============================== --}}
@extends('layouts.admin')

@section('title', 'Detail Unit')

@section('content')
    <x-breadcrumb title="Detail Unit" />

    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">🏫 Detail Unit</h1>

        <div class="mb-4">
            <a href="{{ route('admin.unit.index') }}" class="text-blue-600 hover:underline">
                ← Kembali ke Data Unit
            </a>
        </div>

        <div class="space-y-3 text-gray-700 dark:text-gray-200">
            <div>
                <span class="font-semibold">Nama Unit:</span>
                <p>{{ $unit->nama_unit }}</p>
            </div>

            <div>
                <span class="font-semibold">Tanggal Dibuat:</span>
                <p>{{ $unit->created_at ? $unit->created_at->format('d M Y H:i') : '-' }}</p>
            </div>

            <div>
                <span class="font-semibold">Terakhir Diperbarui:</span>
                <p>{{ $unit->updated_at ? $unit->updated_at->format('d M Y H:i') : '-' }}</p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('admin.unit.edit', $unit->id) }}"
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">
                ✏️ Edit Unit
            </a>
        </div>
    </div>
@endsection
