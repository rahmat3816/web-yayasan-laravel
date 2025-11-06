@extends('layouts.admin')
@section('title', 'Data Halaqoh')

@section('content')
<x-breadcrumb title="Data Halaqoh" />

<div class="flex justify-between items-center mb-6 mt-4">
    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">📖 Data Halaqoh</h1>

    @if (in_array(strtolower(auth()->user()->role), ['superadmin','admin','operator']))
        <a href="{{ route('admin.halaqoh.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">➕ Tambah Halaqoh</a>
    @endif
</div>

@if (session('success'))
    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4 dark:bg-green-800 dark:text-green-100">
        {{ session('success') }}
    </div>
@endif

<div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg">
    <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-2 text-left w-12">#</th>
                <th class="px-4 py-2 text-left">Nama Halaqoh</th>
                <th class="px-4 py-2 text-left">Guru Pembimbing</th>
                <th class="px-4 py-2 text-left">Unit</th>
                <th class="px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($halaqoh as $h)
                <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $h->nama_halaqoh }}</td>
                    <td class="px-4 py-2">{{ $h->guru->nama ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $h->unit->nama_unit ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.halaqoh.show', $h->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Detail</a>
                            @if (in_array(strtolower(auth()->user()->role), ['superadmin','admin','operator']))
                                <a href="{{ route('admin.halaqoh.edit', $h->id) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">Edit</a>
                                <form action="{{ route('admin.halaqoh.destroy', $h->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus halaqoh ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">Belum ada data halaqoh.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
