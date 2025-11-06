{{-- ==============================
📘 Data Santri – Admin & Operator
============================== --}}
@extends('layouts.admin')

@section('title', 'Data Santri')

@section('content')
    {{-- 🔹 Breadcrumb --}}
    <x-breadcrumb title="Data Santri" />

    {{-- 🔹 Header --}}
    <div class="flex justify-between items-center mb-6 mt-4">
        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">📋 Data Santri</h1>

        @if (in_array(strtolower(auth()->user()->role), ['superadmin', 'admin', 'operator']))
            <a href="{{ route('admin.santri.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
                ➕ Tambah Santri
            </a>
        @endif
    </div>

    {{-- 🔹 Notifikasi Sukses --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4 dark:bg-green-800 dark:text-green-100">
            {{ session('success') }}
        </div>
    @endif

    {{-- 🔹 Tabel Data Santri --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg">
        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left w-12">#</th>
                    <th class="px-4 py-2 text-left">Nama Santri</th>
                    <th class="px-4 py-2 text-left">NIS</th>
                    <th class="px-4 py-2 text-left">Jenis Kelamin</th>
                    <th class="px-4 py-2 text-left">Unit</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($santri as $s)
                    <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $s->nama }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $s->nis }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $s->unit->nama_unit ?? '-' }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.santri.show', $s->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition">
                                    Detail
                                </a>

                                @if (in_array(strtolower(auth()->user()->role), ['superadmin', 'admin', 'operator']))
                                    <a href="{{ route('admin.santri.edit', $s->id) }}"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.santri.destroy', $s->id) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus santri ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                            Belum ada data santri.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
