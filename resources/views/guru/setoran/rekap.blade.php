@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Rekap Detail Setoran — Halaqoh Saya</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Total Halaman (unik)</div>
            <div class="text-2xl font-bold">{{ $rekap['total_halaman'] }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Total Juz (unik)</div>
            <div class="text-2xl font-bold">{{ $rekap['total_juz'] }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">Total Surah (unik)</div>
            <div class="text-2xl font-bold">{{ $rekap['total_surah'] }}</div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">Santri</th>
                    <th class="px-3 py-2 text-left">Mode</th>
                    <th class="px-3 py-2 text-left">Detail</th>
                    <th class="px-3 py-2 text-left">Juz</th>
                    <th class="px-3 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $h)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $h->tanggal_setor->format('d M Y') }}</td>
                        <td class="px-3 py-2">{{ $h->santri->nama ?? '-' }}</td>
                        <td class="px-3 py-2 uppercase">{{ $h->mode }}</td>
                        <td class="px-3 py-2">
                            @if($h->mode==='page')
                                Hal. {{ $h->page_start }}–{{ $h->page_end }}
                            @else
                                Surah {{ $h->surah_id }}, Ayat {{ $h->ayah_start }}–{{ $h->ayah_end }}
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $h->juz_start ? $h->juz_start : '-' }} @if($h->juz_end && $h->juz_end!=$h->juz_start) – {{ $h->juz_end }} @endif
                        </td>
                        <td class="px-3 py-2">{{ ucfirst($h->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
