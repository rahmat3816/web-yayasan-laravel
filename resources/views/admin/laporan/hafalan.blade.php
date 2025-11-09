{{-- ==============================
📊 Laporan Hafalan Qur’an (Admin)
============================== --}}
@extends('layouts.admin')

@section('label', 'Laporan Hafalan Qur’an')

@section('content')
<x-breadcrumb label="📖 Laporan Hafalan Qur’an" />

{{-- 🎛️ Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div>
        <label class="block text-sm font-semibold">Tahun</label>
        <select name="tahun" class="border rounded px-2 py-1 pr-8">
            <option value="">Semua Tahun</option>
            @foreach (range(now()->year, now()->year - 4) as $t)
                <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold">Bulan</label>
        <select name="bulan" class="border rounded px-2 py-1 pr-8">
            <option value="">Semua Bulan</option>
            @foreach (range(1, 12) as $b)
                <option value="{{ $b }}" @selected($b == $bulan)>
                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold">Unit</label>
        <select name="unit_id" class="border rounded px-2 py-1 pr-8">
            <option value="">Semua Unit</option>
            @foreach ($units as $u)
                <option value="{{ $u->id }}" @selected($unitId == $u->id)>{{ $u->nama_unit }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold">Guru</label>
        <select name="guru_id" class="border rounded px-2 py-1 pr-8">
            <option value="">Semua Guru</option>
            @foreach ($guruList as $g)
                <option value="{{ $g->id }}" @selected($guruId == $g->id)>{{ $g->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold">Santri</label>
        <select name="santri_id" class="border rounded px-2 py-1 pr-8">
            <option value="">Semua Santri</option>
            @foreach ($santriList as $s)
                <option value="{{ $s->id }}" @selected($santriId == $s->id)>{{ $s->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            🔍 Tampilkan
        </button>
    </div>
</form>

{{-- ==============================
📈 Statistik Ringkas
============================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 mb-8">
    <x-admin.stat label="Total Halaman" :value="$rekap['total_halaman'] ?? 0" color="bg-green-100 dark:bg-green-900" />
    <x-admin.stat label="Total Juz" :value="$rekap['total_juz'] ?? 0" color="bg-indigo-100 dark:bg-indigo-900" />
    <x-admin.stat label="Total Surah" :value="$rekap['total_surah'] ?? 0" color="bg-pink-100 dark:bg-pink-900" />
</div>

{{-- ==============================
📅 Grafik Capaian Harian
============================== --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl p-6 mb-10 shadow">
    <h2 class="text-xl font-semibold mb-4">📅 Capaian Setoran Harian</h2>
    <canvas id="chartHarian" height="100"></canvas>
</div>

{{-- ==============================
📖 Grafik Total Ayat Kumulatif
============================== --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl p-6 mb-10 shadow">
    <h2 class="text-xl font-semibold mb-4">📖 Total Ayat Kumulatif</h2>
    <canvas id="chartTotalAyat" height="100"></canvas>
</div>

{{-- ==============================
📋 Tabel Data Setoran
============================== --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl p-6 shadow overflow-x-auto">
    <h2 class="text-xl font-semibold mb-4">📋 Data Hafalan Santri</h2>
    <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg">
        <thead class="bg-gray-100 dark:bg-gray-800">
            <tr class="text-left">
                <th class="px-4 py-2 border-b">Tanggal</th>
                <th class="px-4 py-2 border-b">Santri</th>
                <th class="px-4 py-2 border-b">Guru</th>
                <th class="px-4 py-2 border-b">Surah</th>
                <th class="px-4 py-2 border-b text-center">Ayat</th>
                <th class="px-4 py-2 border-b text-center">Juz</th>
                <th class="px-4 py-2 border-b">Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($data as $h)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($h->tanggal_setor)->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-2">{{ $h->santri->nama ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $h->guru->nama ?? '-' }}</td>
                    <td class="px-4 py-2">Surah {{ $h->surah_id }}</td>
                    <td class="px-4 py-2 text-center">{{ $h->ayah_start }}–{{ $h->ayah_end }}</td>
                    <td class="px-4 py-2 text-center">{{ $h->juz_start }}</td>
                    <td class="px-4 py-2">{{ $h->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-500">Belum ada data setoran hafalan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const labelsHarian = {!! json_encode($grafikPerHari->keys() ?? []) !!};
    const dataHarian = {!! json_encode($grafikPerHari->values() ?? []) !!};
    const ctxHarian = document.getElementById('chartHarian').getContext('2d');

    new Chart(ctxHarian, {
        type: 'line',
        data: { labels: labelsHarian, datasets: [{
            label: 'Jumlah Setoran per Hari',
            data: dataHarian,
            borderColor: 'rgb(59,130,246)',
            backgroundColor: 'rgba(59,130,246,0.2)',
            fill: true, tension: 0.4
        }]},
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const labelsTotal = {!! json_encode(array_keys($totalAyatPerHari ?? [])) !!};
    const dataTotal = {!! json_encode(array_values($totalAyatPerHari ?? [])) !!};
    const ctxTotal = document.getElementById('chartTotalAyat').getContext('2d');

    new Chart(ctxTotal, {
        type: 'line',
        data: { labels: labelsTotal, datasets: [{
            label: 'Total Ayat Kumulatif',
            data: dataTotal,
            borderColor: 'rgb(34,197,94)',
            backgroundColor: 'rgba(34,197,94,0.2)',
            fill: true, tension: 0.4
        }]},
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush
@endsection
