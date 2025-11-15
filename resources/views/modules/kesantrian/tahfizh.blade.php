@extends('layouts.app')
@section('label', 'Koordinasi ' . $title)

@section('content')
<x-breadcrumb label="Koordinasi {{ $title }}" />

<div class="space-y-8">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-800 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-300">Total Halaqoh</p>
            <p class="mt-2 text-3xl font-semibold text-indigo-700 dark:text-indigo-200">{{ $stats['halaqoh'] }}</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-800 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-300">Guru {{ $genderLabel }}</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-200">{{ $stats['guru'] }}</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-800 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-300">Santri {{ $genderLabel }}</p>
            <p class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-200">{{ $stats['santri'] }}</p>
        </div>
    </div>

    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Daftar Halaqoh ({{ strtoupper($genderCode) }})</h2>
            <span class="text-sm text-gray-500">Guru & santri otomatis difilter sesuai jenis kelamin</span>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Nama Halaqoh</th>
                        <th class="px-4 py-2 text-left">Guru Pengampu</th>
                        <th class="px-4 py-2 text-left">Unit</th>
                        <th class="px-4 py-2 text-left">Jumlah Santri</th>
                        <th class="px-4 py-2 text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($halaqoh as $item)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $item->nama_halaqoh }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $item->guru->nama ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $item->unit->nama_unit ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $item->santri->count() }}</td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-300">{{ $item->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data halaqoh untuk profil ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid gap-6 md:grid-cols-2">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100">Guru {{ $genderLabel }}</h3>
                <span class="text-sm text-gray-500">{{ $stats['guru'] }} guru</span>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Nama Guru</th>
                        <th class="px-4 py-2 text-left">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($guru as $g)
                        <tr>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $g->nama }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $g->unit->nama_unit ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500">Belum ada guru yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100">Santri {{ $genderLabel }}</h3>
                <span class="text-sm text-gray-500">Menampilkan {{ $santri->count() }} dari {{ $santriTotal }} santri</span>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Nama Santri</th>
                        <th class="px-4 py-2 text-left">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($santri as $s)
                        <tr>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ $s->nama }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $s->unit->nama_unit ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500">Belum ada data santri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <p class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800">
                Daftar santri dibatasi 100 nama untuk menjaga performa. Gunakan kontrol panel jika membutuhkan daftar lengkap.
            </p>
        </div>
    </section>
</div>
@endsection
