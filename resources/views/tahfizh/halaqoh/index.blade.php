@extends('layouts.app')

@section('title','Tahfizh - Halaqoh')

@section('content')
<div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-2">
        <a href="{{ route('tahfizh.dashboard') }}" class="hover:underline">Tahfizh</a>
        <span class="mx-1">></span>
        <span class="text-gray-700 font-medium">Halaqoh</span>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold"> Halaqoh</h1>
        <a href="{{ route('tahfizh.halaqoh.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
            + Buat Halaqoh Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3">Guru Pengampu</th>
                    <th class="px-4 py-3">JK</th>
                    <th class="px-4 py-3">Jumlah Santri</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($halaqoh as $h)
                    <tr>
                        <td class="px-4 py-3">H{{ $h->id }}</td>
                        <td class="px-4 py-3">
                            {{ $h->unit->nama_unit ?? ('Unit '.$h->unit_id) }}
                            <span class="text-xs text-gray-400">(ID {{ $h->unit_id }})</span>
                        </td>
                        <td class="px-4 py-3">{{ $h->guru->nama ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php $jk = $h->guru->jenis_kelamin ?? null; @endphp
                            @if($jk === 'L')
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">L</span>
                            @elseif($jk === 'P')
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-pink-100 text-pink-700">P</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $h->santri->count() }}</td>
                        <td class="px-4 py-3">{{ $h->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('tahfizh.halaqoh.pengampu.edit', $h->id) }}"
                               class="text-blue-600 hover:underline">Ubah Pengampu / Santri</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="7">
                            Belum ada data halaqoh.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
