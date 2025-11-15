@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')
@php
    $monthName = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
    $cards = [
        [
            'title' => 'Input Setoran',
            'description' => 'Catat setoran harian santri',
            'url' => route('guru.setoran.index'),
            'icon' => 'clipboard-document-check',
        ],
        [
            'title' => 'Rekap Hafalan',
            'description' => 'Pantau progres per halaqoh',
            'url' => route('guru.setoran.rekap', request()->only('halaqoh_id')),
            'icon' => 'chart-bar',
        ],
        [
            'title' => 'Data Santri',
            'description' => 'Detail santri bimbingan',
            'url' => route('guru.dashboard', request()->only(['bulan', 'tahun'])),
            'icon' => 'users',
        ],
    ];
@endphp

<div class="space-y-8 p-4 lg:p-6">
    <x-admin.alert />

    @if (!empty($errorMessage))
        <div class="card border border-amber-200 bg-amber-50/80 text-amber-800">
            <div class="card-body flex items-start gap-3">
                <span class="rounded-full bg-amber-200/70 p-2 text-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
                <div>
                    <p class="font-semibold">Perlu Verifikasi Data Guru</p>
                    <p class="text-sm text-amber-700/80">
                        {{ $errorMessage }}. Silakan hubungi admin unit untuk memperbarui relasi akun dengan data guru.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-sky-500 to-cyan-400 text-white shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.35),_transparent_55%)] opacity-70"></div>
        <div class="relative flex flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-white/70">Dashboard Guru</p>
                <h1 class="mt-2 text-3xl font-semibold">Pantau Hafalan Santri</h1>
                <p class="mt-3 max-w-2xl text-white/85">
                    Fokus pada progres santri sepanjang bulan {{ $monthName }} {{ $tahun }}. Semua data diambil dari modul setoran hafalan yang sudah berjalan.
                </p>
                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    <span class="badge badge-outline border-white/80 text-white">Total Santri: {{ $totalSantri ?? 0 }}</span>
                    <span class="badge badge-outline border-white/80 text-white">Total Setoran Bulan Ini: {{ $totalHafalan ?? 0 }}</span>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3 lg:max-w-lg">
                @foreach ($cards as $card)
                    <a href="{{ $card['url'] }}"
                       class="group rounded-2xl border border-white/40 bg-white/15 p-4 backdrop-blur transition hover:-translate-y-1 hover:bg-white/25">
                        <div class="mb-3 inline-flex rounded-full bg-white/30 p-2 text-white">
                            @switch($card['icon'])
                                @case('clipboard-document-check')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5h6M9 3h6a2 2 0 012 2v1h1a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h1V5a2 2 0 012-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4" />
                                    </svg>
                                    @break
                                @case('chart-bar')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 19V5m4 14V9m4 10V7m4 12V3" />
                                    </svg>
                                    @break
                                @default
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a6 6 0 00-12 0v2h5M4 20h5v-2a6 6 0 016-6 6 6 0 00-12 0v2h5" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                            @endswitch
                        </div>
                        <p class="text-sm font-semibold">{{ $card['title'] }}</p>
                        <p class="text-xs text-white/80">{{ $card['description'] }}</p>
                        <span class="mt-3 inline-flex items-center text-xs font-semibold text-white/80 group-hover:text-white">
                            Buka modul
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Filter Bulan & Tahun --}}
    <div class="card bg-white/90 shadow-xl text-gray-900">
        <div class="card-body">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="card-title text-lg font-semibold text-gray-900">Filter Periode Hafalan</h2>
                    <p class="text-sm text-gray-500">Pilih bulan & tahun untuk melihat statistik sesuai kebutuhan.</p>
                </div>
                <form method="GET" action="{{ route('guru.dashboard') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                    <div class="form-control text-gray-900">
                        <label for="bulan" class="label-text text-sm font-semibold">Bulan</label>
                        <select name="bulan" id="bulan" class="select select-bordered bg-white text-gray-900">
                            @foreach (range(1, 12) as $b)
                                <option value="{{ $b }}" @selected($b == $bulan)>
                                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control text-gray-900">
                        <label for="tahun" class="label-text text-sm font-semibold">Tahun</label>
                        <select name="tahun" id="tahun" class="select select-bordered bg-white text-gray-900">
                            @foreach (range(date('Y') - 2, date('Y')) as $t)
                                <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary md:self-end">Terapkan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Statistik Utama --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 text-gray-900">
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <p class="text-sm text-gray-500">Santri Bimbingan</p>
                <p class="text-3xl font-semibold text-gray-900">{{ $totalSantri ?? 0 }}</p>
                <p class="text-xs text-gray-500">Total santri aktif dalam halaqoh.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <p class="text-sm text-gray-500">Total Hafalan Bulan {{ $monthName }}</p>
                <p class="text-3xl font-semibold text-gray-900">{{ $totalHafalan ?? 0 }}</p>
                <p class="text-xs text-gray-500">Jumlah setoran yang tercatat.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <p class="text-sm text-gray-500">Target Bulanan</p>
                <p class="text-3xl font-semibold text-gray-900">
                    {{ number_format($targetBulanan) }}
                    <span class="text-sm font-normal text-gray-500">ayat</span>
                </p>
                <p class="text-xs text-gray-500">Target ayat mengacu pada perencanaan tahfizh tahun {{ $tahun }}.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <p class="text-sm text-gray-500">Progress</p>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($progressPersen, 1) }}%</p>
                <p class="text-xs text-gray-500">Persentase capaian ayat bulan {{ $monthName }}.</p>
            </div>
        </div>
    </div>

    {{-- Target Hafalan --}}
    <div class="card bg-white/90 shadow-xl text-gray-900">
        <div class="card-body space-y-4">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-primary">Target Bulanan</p>
                    <h3 class="text-2xl font-semibold text-gray-900">
                        Pencapaian Hafalan {{ $monthName }} {{ $tahun }}
                    </h3>
                </div>
                <span class="badge badge-primary badge-lg text-white">{{ number_format($progressPersen, 1) }}% tercapai</span>
            </div>
            <div class="h-3 w-full rounded-full bg-base-200">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 via-cyan-400 to-blue-500 transition-all duration-700" style="width: {{ min(100, $progressPersen) }}%"></div>
            </div>
            <div class="flex flex-wrap items-center justify-between text-sm text-gray-500">
                @if ($targetBulanan > 0)
                    <span>{{ number_format($totalAyatBulanan) }} dari {{ number_format($targetBulanan) }} ayat bulan ini</span>
                @else
                    <span>Belum ada target tahfizh untuk tahun {{ $tahun }}.</span>
                @endif
                <span>
                    @if($progressPersen >= 100)
                        Target sudah terpenuhi
                    @elseif($progressPersen >= 70)
                        Hampir sampai, pertahankan ritme!
                    @else
                        Tingkatkan lagi setoran pekan ini.
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Rekapan Progres --}}
    <div class="grid gap-4 md:grid-cols-3 text-gray-900">
        <div class="card bg-white/90 shadow-lg text-center">
            <div class="card-body">
                <p class="text-sm font-semibold text-gray-500">Belum Mulai</p>
                <p class="text-4xl font-bold text-gray-900">{{ $totalBelumMulai ?? 0 }}</p>
                <p class="text-xs text-gray-500">Santri tanpa setoran</p>
            </div>
        </div>
        <div class="card bg-white/90 shadow-lg text-center">
            <div class="card-body">
                <p class="text-sm font-semibold text-gray-500">Sedang Berjalan</p>
                <p class="text-4xl font-bold text-amber-500">{{ $totalBerjalan ?? 0 }}</p>
                <p class="text-xs text-gray-500">Setoran sedang berlangsung</p>
            </div>
        </div>
        <div class="card bg-white/90 shadow-lg text-center">
            <div class="card-body">
                <p class="text-sm font-semibold text-gray-500">Selesai</p>
                <p class="text-4xl font-bold text-emerald-500">{{ $totalSelesai ?? 0 }}</p>
                <p class="text-xs text-gray-500">Santri mencapai target</p>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="grid gap-6 lg:grid-cols-2 text-gray-900">
        <div class="card bg-white/90 shadow-xl">
            <div class="card-body">
                <h3 class="card-title text-lg">Progres Hafalan Mingguan</h3>
                <canvas id="hafalanChart" height="160"></canvas>
            </div>
        </div>
        <div class="card bg-white/90 shadow-xl">
            <div class="card-body">
                <h3 class="card-title text-lg">Distribusi Santri per Unit</h3>
                <canvas id="unitChart" height="160"></canvas>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 text-gray-900">
        <div class="card bg-white/90 shadow-xl">
            <div class="card-body">
                <h3 class="card-title text-lg">Rekap Hafalan per Juz</h3>
                <canvas id="juzChart" height="160"></canvas>
            </div>
        </div>
        <div class="card bg-white/90 shadow-xl">
            <div class="card-body">
                <h3 class="card-title text-lg">10 Surah Paling Sering Disetorkan</h3>
                <canvas id="suratChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabel Santri --}}
    @if(($daftarSantri ?? collect())->isNotEmpty())
        <div class="card bg-white/90 shadow-2xl text-gray-900">
            <div class="card-body overflow-x-auto">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="card-title text-lg">Daftar Santri Bimbingan</h3>
                        <p class="text-sm text-gray-500">Periode {{ $monthName }} {{ $tahun }}</p>
                    </div>
                    <span class="badge badge-outline">{{ $daftarSantri->count() }} santri terdata</span>
                </div>
                <div class="mt-4 overflow-hidden rounded-2xl border border-base-200">
                    <table class="min-w-full text-sm text-gray-900">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama Santri</th>
                                <th class="px-4 py-3 text-left">Unit</th>
                                <th class="px-4 py-3 text-center">Jumlah Setoran</th>
                                <th class="px-4 py-3 text-center">Terakhir Setor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($daftarSantri as $index => $santri)
                                <tr class="border-t border-gray-100 hover:bg-gray-100">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $santri->nama_santri }}</td>
                                    <td class="px-4 py-3">{{ $santri->unit ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ $santri->total_hafalan }}</td>
                                    <td class="px-4 py-3 text-center">
                                        {{ $santri->terakhir_setor ? \Carbon\Carbon::parse($santri->terakhir_setor)->translatedFormat('d M Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hafalanData = {!! json_encode($hafalanPerMinggu ?? []) !!};
    const unitData = {!! json_encode($santriPerUnit ?? []) !!};
    const juzData = {!! json_encode($rekapPerJuz ?? []) !!};
    const suratData = {!! json_encode($rekapSurat ?? []) !!};

    const ctxH = document.getElementById('hafalanChart').getContext('2d');
    new Chart(ctxH, {
        type: 'line',
        data: {
            labels: Object.keys(hafalanData).length ? Object.keys(hafalanData) : ['Minggu 1'],
            datasets: [{
                label: 'Total Hafalan per Minggu',
                data: Object.values(hafalanData).length ? Object.values(hafalanData) : [0],
                borderColor: 'rgb(59,130,246)',
                backgroundColor: 'rgba(59,130,246,0.15)',
                tension: 0.35,
                fill: true
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    if (Object.keys(unitData).length > 0) {
        const ctxU = document.getElementById('unitChart').getContext('2d');
        new Chart(ctxU, {
            type: 'bar',
            data: {
                labels: Object.keys(unitData),
                datasets: [{
                    data: Object.values(unitData),
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 8
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    if (Object.keys(juzData).length > 0) {
        const ctxJ = document.getElementById('juzChart').getContext('2d');
        new Chart(ctxJ, {
            type: 'bar',
            data: {
                labels: Object.keys(juzData),
                datasets: [{
                    data: Object.values(juzData),
                    backgroundColor: 'rgba(249,115,22,0.7)',
                    borderRadius: 8
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    if (Object.keys(suratData).length > 0) {
        const ctxS = document.getElementById('suratChart').getContext('2d');
        new Chart(ctxS, {
            type: 'bar',
            data: {
                labels: Object.keys(suratData),
                datasets: [{
                    data: Object.values(suratData),
                    backgroundColor: 'rgba(99,102,241,0.8)',
                    borderRadius: 8
                }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
});
</script>
@endpush
