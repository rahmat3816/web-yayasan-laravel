{{-- ==============================
📊 Rekap Detail Setoran Hafalan (Guru)
Sinkron dengan SetoranHafalanController (tanpa mode halaman)
============================== --}}

@extends('layouts.app')

@section('title', 'Rekap Setoran Hafalan')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">📖 Rekap Detail Setoran — Halaqoh Saya</h1>

    {{-- ✅ Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Total Juz</div>
            <div class="text-2xl font-bold">{{ $rekap['total_juz'] ?? 0 }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Total Surah</div>
            <div class="text-2xl font-bold">{{ $rekap['total_surah'] ?? 0 }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Jumlah Setoran</div>
            <div class="text-2xl font-bold">{{ $data->count() }}</div>
        </div>
    </div>

    {{-- 🧾 Tabel Data --}}
    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">Santri</th>
                    <th class="px-3 py-2 text-left">Surah</th>
                    <th class="px-3 py-2 text-left">Ayat</th>
                    <th class="px-3 py-2 text-left">Juz</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $h)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($h->tanggal_setor)->format('d M Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $h->santri->nama ?? '-' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $h->surah_id ? sprintf('%03d', $h->surah_id) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            Ayat {{ $h->ayah_start }}–{{ $h->ayah_end }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $h->juz_start }}
                        </td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $h->status === 'lulus' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($h->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">
                            {{ $h->catatan ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                            Belum ada data setoran hafalan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <a href="{{ route('guru.setoran.index') }}"
           class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm">
            ⬅️ Kembali ke Daftar Santri
        </a>
    </div>
</div>
@endsection
