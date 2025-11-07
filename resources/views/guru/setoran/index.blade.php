@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Setoran Hafalan — Halaqoh</h1>

    {{-- 🔔 Notifikasi global (sukses, error, warning) --}}
    <x-admin.alert />

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    @if($isSuper && $allHalaqoh->count())
        <form method="GET" action="{{ route('guru.setoran.index') }}" class="mb-4 flex items-center gap-2">
            <label class="text-sm text-gray-600">Pilih Halaqoh:</label>
            <select name="halaqoh_id" class="border rounded px-3 py-2">
                @foreach($allHalaqoh as $h)
                    <option value="{{ $h->id }}" @selected(request('halaqoh_id', $halaqoh->id ?? 0) == $h->id)>
                        H{{ $h->id }} — Guru: {{ $h->guru->nama ?? '—' }} (Unit {{ $h->unit_id }})
                    </option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Lihat</button>
        </form>
    @endif

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

    <div class="bg-white dark:bg-gray-800 rounded shadow">
        <div class="p-4 border-b font-semibold">Santri dalam Halaqoh</div>
        <div class="divide-y">
            @forelse($santri as $s)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $s->nama }}</div>
                    </div>

                    @if(!$isSuper)
                        <a href="{{ route('guru.setoran.create', $s->id) }}"
                           class="inline-block px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                            + Setor Baru
                        </a>
                    @else
                        <span class="text-xs text-gray-500">View-only</span>
                    @endif
                </div>
            @empty
                <div class="p-4 text-gray-500">Belum ada santri pada halaqoh ini.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('guru.setoran.rekap', array_filter(['halaqoh_id' => request('halaqoh_id', $halaqoh->id ?? null)])) }}"
           class="text-blue-600 hover:underline">Lihat Rekap Detail</a>
    </div>
</div>
@endsection
