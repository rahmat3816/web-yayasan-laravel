{{-- ==============================
📘 Guru Dashboard (Versi Lengkap + Rekapan Progres)
============================== --}}

@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')
@if (!empty($errorMessage))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg shadow">
        <p class="font-semibold">⚠️ {{ $errorMessage }}</p>
        <p class="text-sm mt-1 text-gray-600">Hubungi admin unit untuk memperbarui data guru terkait akun ini.</p>
    </div>
@endif

{{-- 🎛️ Filter Bulan & Tahun --}}
<form method="GET" action="{{ route('guru.dashboard') }}" class="mb-8 flex flex-wrap items-end gap-3">
    <div>
        <label for="bulan" class="block text-sm font-semibold">Bulan</label>
        <select name="bulan" id="bulan" class="border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
            @foreach (range(1, 12) as $b)
                <option value="{{ $b }}" {{ $b == $bulan ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="tahun" class="block text-sm font-semibold">Tahun</label>
        <select name="tahun" id="tahun" class="border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
            @foreach (range(date('Y') - 2, date('Y')) as $t)
                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit"
        class="h-10 px-5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
        🔍 Tampilkan
    </button>
</form>

{{-- Statistik Utama --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="p-4 bg-indigo-100 dark:bg-indigo-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Santri Bimbingan</h3>
        <p class="text-3xl font-bold">{{ $totalSantri ?? 0 }}</p>
    </div>
    <div class="p-4 bg-teal-100 dark:bg-teal-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">
            Total Hafalan ({{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }})
        </h3>
        <p class="text-3xl font-bold">{{ $totalHafalan ?? 0 }}</p>
    </div>
</div>

{{-- ============================== --}}
{{-- 🎯 Target Hafalan Bulanan --}}
{{-- ============================== --}}
<div class="mt-8 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-3">
        🎯 Target Hafalan Bulan {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
    </h2>

    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-5 overflow-hidden">
        <div class="bg-green-500 h-5 rounded-full transition-all duration-700 ease-out"
             style="width: {{ $progressPersen }}%"></div>
    </div>

    <div class="flex justify-between mt-2 text-sm text-gray-600 dark:text-gray-400">
        <span>{{ $totalHafalan }} dari {{ $targetBulanan }} setoran tercapai</span>
        <span>{{ $progressPersen }}%</span>
    </div>

    @if($progressPersen >= 100)
        <div class="mt-3 text-green-700 dark:text-green-400 font-semibold">
            🏆 Alhamdulillah! Target bulan ini telah tercapai.
        </div>
    @elseif($progressPersen >= 70)
        <div class="mt-3 text-blue-700 dark:text-blue-400 font-semibold">
            💪 Hampir tercapai, semangat sedikit lagi!
        </div>
    @else
        <div class="mt-3 text-yellow-700 dark:text-yellow-400 font-semibold">
            🌱 Terus tingkatkan setoran hafalan bulan ini.
        </div>
    @endif
</div>

{{-- 🧮 Rekapan Progres Hafalan --}}
<div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-2xl shadow text-center">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">⏸️ Belum Mulai</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">{{ $totalBelumMulai ?? 0 }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Santri tanpa setoran</p>
    </div>

    <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-2xl shadow text-center">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">🔄 Sedang Berjalan</h3>
        <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-300 mt-2">{{ $totalBerjalan ?? 0 }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Proses hafalan sedang berlangsung</p>
    </div>

    <div class="p-4 bg-green-100 dark:bg-green-900 rounded-2xl shadow text-center">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">✅ Selesai</h3>
        <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-2">{{ $totalSelesai ?? 0 }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Telah menyelesaikan hafalan</p>
    </div>
</div>

{{-- Grafik --}}
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">📖 Progres Hafalan Mingguan</h2>
    <canvas id="hafalanChart" height="120"></canvas>
</div>

@if(($santriPerUnit ?? collect())->isNotEmpty())
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">🏫 Distribusi Santri per Unit</h2>
    <canvas id="unitChart" height="100"></canvas>
</div>
@endif

@if(($rekapPerJuz ?? collect())->isNotEmpty())
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">🧮 Rekap Hafalan per Juz</h2>
    <canvas id="juzChart" height="100"></canvas>
</div>
@endif

@if(($rekapSurat ?? collect())->isNotEmpty())
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">🕋 10 Surat Paling Sering Disetorkan</h2>
    <canvas id="suratChart" height="100"></canvas>
</div>
@endif

{{-- 🧾 Daftar Santri --}}
@if(($daftarSantri ?? collect())->isNotEmpty())
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6 overflow-x-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">👨‍🎓 Daftar Santri Bimbingan</h2>
        <p class="text-sm text-gray-500">
            Periode: {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
        </p>
    </div>
    <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
        <thead class="bg-gray-100 dark:bg-gray-800">
            <tr class="text-left text-gray-700 dark:text-gray-200">
                <th class="px-4 py-2 border-b">No</th>
                <th class="px-4 py-2 border-b">Nama Santri</th>
                <th class="px-4 py-2 border-b">Unit</th>
                <th class="px-4 py-2 border-b text-center">Jumlah Hafalan</th>
                <th class="px-4 py-2 border-b text-center">Terakhir Setor</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($daftarSantri as $index => $santri)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 font-medium">{{ $santri->nama_santri }}</td>
                    <td class="px-4 py-2">{{ $santri->unit ?? '-' }}</td>
                    <td class="px-4 py-2 text-center">{{ $santri->total_hafalan }}</td>
                    <td class="px-4 py-2 text-center">
                        {{ $santri->terakhir_setor ? \Carbon\Carbon::parse($santri->terakhir_setor)->translatedFormat('d M Y') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hafalanData = {!! json_encode($hafalanPerMinggu ?? []) !!};
    const unitData = {!! json_encode($santriPerUnit ?? []) !!};
    const juzData = {!! json_encode($rekapPerJuz ?? []) !!};
    const suratData = {!! json_encode($rekapSurat ?? []) !!};

    // Hafalan Mingguan
    const ctxH = document.getElementById('hafalanChart').getContext('2d');
    new Chart(ctxH, {
        type: 'line',
        data: {
            labels: Object.keys(hafalanData).length ? Object.keys(hafalanData) : ['Minggu 1'],
            datasets: [{
                label: 'Total Hafalan per Minggu',
                data: Object.values(hafalanData).length ? Object.values(hafalanData) : [0],
                borderColor: 'rgb(34,197,94)',
                backgroundColor: 'rgba(34,197,94,0.25)',
                tension: 0.35,
                fill: true
            }]
        }
    });

    // Santri per Unit
    if (Object.keys(unitData).length > 0) {
        const ctxU = document.getElementById('unitChart').getContext('2d');
        new Chart(ctxU, {
            type: 'bar',
            data: {
                labels: Object.keys(unitData),
                datasets: [{
                    data: Object.values(unitData),
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // Rekap Juz
    if (Object.keys(juzData).length > 0) {
        const ctxJ = document.getElementById('juzChart').getContext('2d');
        new Chart(ctxJ, {
            type: 'bar',
            data: {
                labels: Object.keys(juzData),
                datasets: [{
                    data: Object.values(juzData),
                    backgroundColor: 'rgba(245,158,11,0.7)',
                    borderRadius: 6
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // Rekap Surat
    if (Object.keys(suratData).length > 0) {
        const ctxS = document.getElementById('suratChart').getContext('2d');
        new Chart(ctxS, {
            type: 'bar',
            data: {
                labels: Object.keys(suratData),
                datasets: [{
                    data: Object.values(suratData),
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 6
                }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
});
</script>
@endpush
