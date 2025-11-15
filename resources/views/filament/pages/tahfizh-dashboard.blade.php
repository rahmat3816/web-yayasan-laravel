<x-filament::page>
<div class="tahfizh-dashboard space-y-6 max-w-6xl mx-auto w-full">
{{-- ==============================
Tahfizh Dashboard (Versi Terbaru)
Tujuan: Menampilkan data real hafalan per halaqoh menggunakan Chart.js
File: resources/views/tahfizh/dashboard.blade.php
============================== --}}

<x-breadcrumb />

<section class="filament-hero">
    <div class="filament-hero__meta">
        <p class="filament-hero__eyebrow">Panel Tahfizh Putri</p>
        <h1 class="filament-hero__title">Ringkasan Hafalan & Target Santri</h1>
        <p class="filament-hero__subtitle">
            Kelola halaqoh, lihat progres halaqoh, dan capaian target hafalan secara real-time.
        </p>
    </div>
    <div class="filament-hero__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 17V7m6 10V4m5 13V9M4 17v-3"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M4 19h16a1 1 0 0 0 1-1v-9.5"/>
        </svg>
    </div>
</section>

@php
    $cards = [
        [
            'title' => 'Tambah Halaqoh',
            'description' => 'Buat halaqoh baru dan tetapkan pengampu.',
            'url' => route('filament.admin.resources.tahfizh.halaqoh.create'),
            'icon' => '',
        ],
        [
            'title' => 'Kelola Halaqoh',
            'description' => 'Atur guru pengampu dan santri.',
            'url' => route('filament.admin.resources.tahfizh.halaqoh.index'),
            'icon' => '',
        ],
        [
            'title' => 'Atur Target Hafalan',
            'description' => 'Tetapkan target hafalan tahunan santri.',
            'url' => route('filament.admin.pages.tahfizh.perencanaan'),
            'icon' => '',
        ],
        [
            'title' => 'Kelola Setoran Hafalan',
            'description' => 'Input dan pantau setoran harian santri.',
            'url' => route('filament.admin.pages.tahfizh.setoran-hafalan'),
            'icon' => '',
        ],
    ];
@endphp

@php
    $monthlyConfig = $percentageSeries['monthly'] ?? [];
    $semesterConfig = $percentageSeries['semester'] ?? [];
    $monthlyOptions = $monthlyConfig['options'] ?? [];
    $semesterOptions = $semesterConfig['options'] ?? [];
    $monthlyCurrentLabel = collect($monthlyOptions)->firstWhere('key', $monthlyConfig['current_key'] ?? null)['label'] ?? 'Bulan Berjalan';
    $semesterCurrentLabel = collect($semesterOptions)->firstWhere('key', $semesterConfig['current_key'] ?? null)['label'] ?? 'Semester Berjalan';
@endphp

@include('dashboard.partials.action-cards', ['cards' => $cards])

{{-- Statistik Utama --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="filament-card stat-card">
        <h3 class="text-lg font-semibold">Total Halaqoh</h3>
        <p class="text-3xl font-bold">{{ $totalHalaqoh }}</p>
    </div>
    <div class="filament-card stat-card">
        <h3 class="text-lg font-semibold">Total Santri</h3>
        <p class="text-3xl font-bold">{{ $totalSantri }}</p>
    </div>
    <div class="filament-card stat-card">
        <h3 class="text-lg font-semibold">Total Setoran</h3>
        <p class="text-3xl font-bold">{{ $totalHafalan }}</p>
    </div>
</div>

{{-- Grafik Hafalan --}}
<div class="mt-10 grid gap-6">
    <div class="filament-card widget-card chart-card">
        <h2 class="text-2xl font-semibold mb-4">Hafalan per Halaqoh</h2>
        <canvas id="halaqohChart" height="200"></canvas>
    </div>
</div>

<div class="mt-10 filament-card widget-card chart-card">
    @php $hasTimeline = !empty($santriTimeline['datasets']); @endphp
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Riwayat Setoran per Santri</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Data kumulatif ayat yang disetorkan.</p>
        </div>
        @if($santriCandidates->count())
            <div class="flex flex-col gap-1 text-sm text-slate-500 dark:text-slate-400">
                <label for="timeline-picker-select" class="font-medium">Pilih santri</label>
                <select id="timeline-picker-select"
                        class="filament-select w-64 max-w-full"
                        data-endpoint="{{ route('filament.admin.pages.tahfizh-dashboard.timeline') }}">
                    @foreach ($santriCandidates as $candidate)
                        <option value="{{ $candidate->id }}" {{ $candidate->nama === $selectedSantriName ? 'selected' : '' }}>
                            {{ $candidate->nama }} — {{ number_format($candidate->total_ayat) }} ayat
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
    @if (!$hasTimeline)
        <div class="text-center text-slate-400 py-8" id="timeline-empty-state">Belum ada data riwayat setoran.</div>
    @else
        <div id="timeline-empty-state" class="hidden"></div>
    @endif
    <canvas id="santriTimelineChart" height="300" class="{{ $hasTimeline ? '' : 'hidden' }}"></canvas>
</div>

<div class="mt-10 filament-card widget-card chart-card">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Capaian Target Hafalan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbandingan target tahunan vs realisasi setoran.</p>
        </div>
    </div>
    <canvas id="targetProgressChart" height="300"></canvas>
</div>

<div class="mt-10 grid gap-6 lg:grid-cols-3">
    <div class="filament-card widget-card chart-card">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Bulanan</h2>
                <p class="text-xs text-slate-500">Lihat capaian target bulanan santri.</p>
            </div>
            <div class="flex flex-col gap-1 text-sm text-slate-500 dark:text-slate-400">
                <label for="monthlyPeriodSelect" class="font-medium">Periode</label>
                <select id="monthlyPeriodSelect" class="filament-select">
                    @forelse ($monthlyOptions as $option)
                        <option value="{{ $option['key'] }}" {{ $option['key'] === ($monthlyConfig['current_key'] ?? null) ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @empty
                        <option value="" disabled selected>Belum ada data</option>
                    @endforelse
                </select>
            </div>
        </div>
        <canvas id="monthlyPercentageChart" height="200"></canvas>
    </div>
    <div class="filament-card widget-card chart-card">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Semester</h2>
                <p class="text-xs text-slate-500">Lihat capaian target semester santri.</p>
            </div>
            <div class="flex flex-col gap-1 text-sm text-slate-500 dark:text-slate-400">
                <label for="semesterPeriodSelect" class="font-medium">Periode</label>
                <select id="semesterPeriodSelect" class="filament-select">
                    @forelse ($semesterOptions as $option)
                        <option value="{{ $option['key'] }}" {{ $option['key'] === ($semesterConfig['current_key'] ?? null) ? 'selected' : '' }}>
                            {{ $option['label'] }}
                        </option>
                    @empty
                        <option value="" disabled selected>Belum ada data</option>
                    @endforelse
                </select>
            </div>
        </div>
        <canvas id="semesterPercentageChart" height="200"></canvas>
    </div>
    <div class="filament-card widget-card chart-card">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Tahunan</h2>
                <p class="text-xs text-slate-500">Lihat capaian tahunan santri.</p>
            </div>
            <div class="flex flex-col gap-1 text-sm text-slate-500 dark:text-slate-400">
                <label for="annualPeriodSelect" class="font-medium">Periode</label>
                <select id="annualPeriodSelect" class="filament-select">
                    <option value="current" selected>Tahun Berjalan</option>
                    <option value="previous">Tahun Sebelumnya</option>
                </select>
            </div>
        </div>
        <canvas id="annualPercentageChart" height="200"></canvas>
    </div>
</div>

@if(!empty($actualCoverageSummary))
<div class="mt-12 filament-card widget-card">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Capaian Hafalan Aktual</h3>
            <p class="text-sm text-slate-500 dark:text-slate-300">Rekap total juz, halaman, surah, dan ayat dari setoran.</p>
        </div>
    </div>
    <div class="overflow-x-auto actual-coverage-table mt-6" data-coverage-endpoint="{{ route('filament.admin.pages.tahfizh-dashboard.coverage-detail', '__SANTRI__') }}">
        <table class="tahfizh-table">
            <thead>
                <tr>
                    <th>Santri</th>
                    <th>Total Juz</th>
                    <th>Total Halaman</th>
                    <th>Total Surah</th>
                    <th>Total Ayat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($actualCoverageSummary as $summary)
                    <tr>
                        <td class="font-semibold text-slate-800">{{ $summary['santri'] }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_juz'], 1) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_halaman']) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_surah']) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_ayat']) }}</td>
                        <td class="text-center">
                            @php
                                $rekapQuery = array_filter([
                                    'halaqoh_id' => $summary['halaqoh_id'] ?? null,
                                    'santri_id' => $summary['santri_id'] ?? null,
                                ]);
                            @endphp
                            <a href="{{ route('filament.admin.pages.tahfizh.setoran-hafalan.rekap', $rekapQuery) }}"
                               class="filament-btn filament-btn--ghost filament-btn--xs">
                                Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<dialog id="coverageDetailModal" class="modal">
    <div class="modal-box max-w-3xl bg-white dark:bg-slate-900">
        <h3 class="font-bold text-lg text-slate-800 dark:text-white" id="coverageDetailTitle">Detail Hafalan</h3>
        <div class="py-4 max-h-96 overflow-y-auto" id="coverageDetailBody">
            <p class="text-sm text-slate-500">Memuat data...</p>
        </div>
        <div class="modal-action">
            <form method="dialog"><button class="filament-btn filament-btn--ghost">Tutup</button></form>
        </div>
    </div>
</dialog>

{{-- ==============================
Penjelasan
- Statistik atas menampilkan total halaqoh, santri, dan hafalan.
- Grafik bar menampilkan jumlah hafalan per halaqoh secara real-time dari tabel hafalan_quran.
- Menggunakan warna ungu (Tailwind indigo/purple) agar berbeda dari dashboard guru.
- Data otomatis menyesuaikan isi database tanpa perlu ubah kode.
============================== --}}

</div>
</x-filament::page>

@push('styles')
    <style>
        .tahfizh-dashboard {
            --filament-card-bg: rgba(255, 255, 255, 0.96);
            --filament-card-border: rgba(15, 23, 42, 0.08);
            --filament-card-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
            --filament-hero-bg: linear-gradient(135deg, #fdf2f8 0%, #f0f9ff 35%, #e0f2fe 65%, #fef3c7 100%);
            --filament-hero-border: rgba(59, 130, 246, 0.25);
            --filament-text-muted: #64748b;
            --filament-heading: #0f172a;
            --hero-overlay: linear-gradient(120deg, rgba(14,165,233,0.18), rgba(236,72,153,0.18));
            --stat-glow-1: linear-gradient(135deg, rgba(59,130,246,0.25), transparent 70%);
            --stat-glow-2: linear-gradient(135deg, rgba(14,165,233,0.25), transparent 70%);
            --stat-glow-3: linear-gradient(135deg, rgba(249,115,22,0.25), transparent 70%);
            --card-ambient: linear-gradient(135deg, rgba(79,70,229,0.12), rgba(16,185,129,0.12));
            --timeline-bg: radial-gradient(circle at top, rgba(59,130,246,0.14), transparent 60%);
        }

        html.dark .tahfizh-dashboard {
            --filament-card-bg: rgba(2, 6, 23, 0.92);
            --filament-card-border: rgba(148, 163, 184, 0.18);
            --filament-card-shadow: 0 25px 45px rgba(0, 0, 0, 0.45);
            --filament-hero-bg: linear-gradient(140deg, #0f172a 0%, #0b1120 45%, #172554 100%);
            --filament-hero-border: rgba(56, 189, 248, 0.35);
            --filament-text-muted: rgba(226, 232, 240, 0.75);
            --filament-heading: #e2e8f0;
            --hero-overlay: linear-gradient(130deg, rgba(56,189,248,0.25), rgba(236,72,153,0.25));
            --stat-glow-1: linear-gradient(135deg, rgba(59,130,246,0.3), transparent 70%);
            --stat-glow-2: linear-gradient(135deg, rgba(14,165,233,0.3), transparent 70%);
            --stat-glow-3: linear-gradient(135deg, rgba(249,115,22,0.3), transparent 70%);
            --card-ambient: linear-gradient(135deg, rgba(59,130,246,0.18), rgba(159,18,57,0.18));
            --timeline-bg: radial-gradient(circle at top, rgba(56,189,248,0.2), transparent 60%);
        }

        .tahfizh-dashboard .filament-hero {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 2rem;
            border-radius: 1.75rem;
            background: var(--filament-hero-bg);
            border: 1px solid var(--filament-hero-border);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 35px 65px rgba(15,23,42,0.2);
            position: relative;
            overflow: hidden;
        }

        .tahfizh-dashboard .filament-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--hero-overlay);
            pointer-events: none;
        }

        .tahfizh-dashboard .filament-hero__meta {
            flex: 1 1 280px;
            position: relative;
            z-index: 1;
        }

        .tahfizh-dashboard .filament-hero__eyebrow {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #2563eb;
        }

        html.dark .tahfizh-dashboard .filament-hero__eyebrow {
            color: #38bdf8;
        }

        .tahfizh-dashboard .filament-hero__title {
            font-size: clamp(1.5rem, 2.5vw, 2.25rem);
            font-weight: 700;
            margin-top: 0.4rem;
            margin-bottom: 0.8rem;
            color: var(--filament-heading);
        }

        .tahfizh-dashboard .filament-hero__subtitle {
            color: var(--filament-text-muted);
            max-width: 520px;
            line-height: 1.5;
        }

        .tahfizh-dashboard .filament-hero__icon {
            width: 96px;
            height: 96px;
            flex: 0 0 auto;
            border-radius: 2rem;
            background: radial-gradient(circle, rgba(255,255,255,0.35), transparent 70%);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #2563eb;
            display: grid;
            place-items: center;
            position: relative;
            z-index: 1;
        }

        html.dark .tahfizh-dashboard .filament-hero__icon {
            border-color: rgba(14, 165, 233, 0.5);
            color: #38bdf8;
        }

        .tahfizh-dashboard .filament-card {
            background-color: var(--filament-card-bg);
            border: 1px solid var(--filament-card-border);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: var(--filament-card-shadow);
            color: var(--filament-heading);
            position: relative;
            overflow: hidden;
        }

        .tahfizh-dashboard .filament-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--card-ambient);
            opacity: 0.4;
            pointer-events: none;
        }

        .tahfizh-dashboard .filament-card > * {
            position: relative;
            z-index: 1;
        }

        .tahfizh-dashboard .stat-card {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            border-left: 4px solid rgba(79, 70, 229, 0.4);
            position: relative;
        }

        .tahfizh-dashboard .stat-card:nth-child(2) {
            border-left-color: rgba(14, 165, 233, 0.45);
        }

        .tahfizh-dashboard .stat-card:nth-child(3) {
            border-left-color: rgba(249, 115, 22, 0.5);
        }

        .tahfizh-dashboard .stat-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--stat-glow-1);
            opacity: 0.35;
            pointer-events: none;
        }

        .tahfizh-dashboard .stat-card:nth-child(2)::before {
            background: var(--stat-glow-2);
        }

        .tahfizh-dashboard .stat-card:nth-child(3)::before {
            background: var(--stat-glow-3);
        }

        .tahfizh-dashboard .stat-card h3 {
            color: var(--filament-text-muted);
        }

        .tahfizh-dashboard .stat-card p {
            color: var(--filament-heading);
        }

        .tahfizh-dashboard .widget-card h2,
        .tahfizh-dashboard .widget-card h3 {
            color: var(--filament-heading);
        }

        .tahfizh-dashboard .widget-card p {
            color: var(--filament-text-muted);
        }

        .tahfizh-dashboard .widget-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--card-ambient);
            opacity: 0.25;
            pointer-events: none;
        }

        .tahfizh-dashboard .chart-card {
            position: relative;
            overflow: hidden;
        }

        .tahfizh-dashboard .chart-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: var(--timeline-bg);
            opacity: 0.4;
            pointer-events: none;
        }

        .tahfizh-dashboard .chart-card > * {
            position: relative;
            z-index: 1;
        }

        .tahfizh-dashboard .actual-coverage-table {
            max-height: 24rem;
            overflow-y: auto;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(255,255,255,0.7);
        }

        html.dark .tahfizh-dashboard .actual-coverage-table {
            border-color: rgba(148, 163, 184, 0.3);
            background: rgba(15,23,42,0.35);
        }

        .tahfizh-dashboard .actual-coverage-table .tahfizh-table {
            min-width: 720px;
            width: 100%;
            border-collapse: collapse;
        }

        .tahfizh-dashboard .actual-coverage-table .tahfizh-table thead th {
            text-align: left;
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 0.85rem 1rem;
            color: var(--filament-text-muted);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
        }

        .tahfizh-dashboard .actual-coverage-table .tahfizh-table tbody td {
            padding: 1rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.17);
        }

        .tahfizh-dashboard .actual-coverage-table .tahfizh-table tbody tr:hover {
            background-color: rgba(148, 163, 184, 0.08);
        }

        html.dark .tahfizh-dashboard .actual-coverage-table .tahfizh-table tbody tr:hover {
            background-color: rgba(56, 189, 248, 0.1);
        }

        .tahfizh-dashboard .actual-coverage-table::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .tahfizh-dashboard .actual-coverage-table::-webkit-scrollbar-track {
            background: transparent;
        }

        .tahfizh-dashboard .actual-coverage-table::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.35);
            border-radius: 999px;
        }

        .tahfizh-dashboard .widget-card h2 {
            color: inherit;
        }

        .tahfizh-dashboard .filament-select {
            width: 100%;
            border: 1px solid var(--filament-card-border);
            border-radius: 999px;
            padding: 0.4rem 2.5rem 0.4rem 0.9rem;
            font-size: 0.875rem;
            background-color: var(--filament-card-bg);
            color: inherit;
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, currentColor 50%), linear-gradient(135deg, currentColor 50%, transparent 50%);
            background-position: calc(100% - 20px) calc(50% - 3px), calc(100% - 14px) calc(50% - 3px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
        }

        .tahfizh-dashboard .filament-select:focus {
            outline: 2px solid rgba(79, 70, 229, 0.4);
            outline-offset: 2px;
        }

        .tahfizh-dashboard .filament-input {
            width: 100%;
            border: 1px solid var(--filament-card-border);
            border-radius: 0.85rem;
            padding: 0.55rem 0.9rem;
            background-color: var(--filament-card-bg);
            color: inherit;
            font-size: 0.9rem;
        }

        .tahfizh-dashboard .filament-input:focus {
            outline: 2px solid rgba(99, 102, 241, 0.35);
            outline-offset: 2px;
        }

        .tahfizh-dashboard .filament-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.45rem 1.3rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .tahfizh-dashboard .filament-btn--primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
        }

        .tahfizh-dashboard .filament-btn--ghost {
            background: transparent;
            color: inherit;
            border-color: var(--filament-card-border);
        }

        .tahfizh-dashboard .filament-btn--soft {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            border-color: transparent;
        }

        html.dark .tahfizh-dashboard .filament-btn--soft {
            background: rgba(52, 211, 153, 0.12);
            color: #34d399;
        }

        .tahfizh-dashboard .filament-btn--xs {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }

        .tahfizh-dashboard .glass-card {
            background: var(--filament-card-bg);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .tahfizh-dashboard .glass-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(236,72,153,0.18), rgba(14,165,233,0.18));
            opacity: 0.8;
            pointer-events: none;
        }

        .tahfizh-dashboard .glass-card > * {
            position: relative;
            z-index: 1;
        }

        .tahfizh-dashboard .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.2);
        }

        .tahfizh-dashboard .action-card-grid {
            margin-top: 1.5rem;
            margin-bottom: 0;
            gap: 1.25rem;
        }

        .tahfizh-dashboard .widget-card {
            min-width: 0;
        }

        .tahfizh-dashboard .chart-card canvas {
            width: 100% !important;
        }

        .tahfizh-dashboard .planner-shell {
            background: var(--filament-card-bg);
            border: 1px solid var(--filament-card-border);
            border-radius: 1.5rem;
            box-shadow: var(--filament-card-shadow);
            padding: 1.75rem;
        }

        .tahfizh-dashboard .planner-shell .section-eyebrow {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4em;
            color: #0f766e;
            font-weight: 600;
        }

        html.dark .tahfizh-dashboard .planner-shell .section-eyebrow {
            color: #2dd4bf;
        }

        .tahfizh-dashboard .planner-chip {
            border-radius: 999px;
            padding: 0.45rem 1.5rem;
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #0f766e;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.08);
        }

        html.dark .tahfizh-dashboard .planner-chip {
            border-color: rgba(45, 212, 191, 0.4);
            color: #2dd4bf;
            background: rgba(45, 212, 191, 0.08);
        }

        .tahfizh-dashboard .targets-card {
            margin-top: 1.5rem;
            border-radius: 1.75rem;
            border: 1px solid rgba(99, 102, 241, 0.18);
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(248,248,255,0.9));
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        html.dark .tahfizh-dashboard .targets-card {
            background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(17,24,39,0.95));
            border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
        }

        .tahfizh-dashboard .targets-card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.75rem;
        }

        .tahfizh-dashboard .targets-card thead th {
            font-size: 0.8rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--filament-text-muted);
            padding-bottom: 0.75rem;
            font-weight: 700;
        }

        .tahfizh-dashboard .targets-card tbody td {
            padding: 0.85rem 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .tahfizh-dashboard .targets-card tbody td:first-child {
            font-weight: 600;
            color: inherit;
        }

        .tahfizh-dashboard .targets-card tbody td + td {
            padding-left: 1rem;
        }

        .tahfizh-dashboard .targets-card tbody tr:last-child td {
            border-bottom: none;
        }

        .tahfizh-dashboard .targets-card tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.06);
        }

        html.dark .tahfizh-dashboard .targets-card tbody tr:hover {
            background-color: rgba(148, 163, 184, 0.08);
        }

        .tahfizh-dashboard .targets-card__header {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .tahfizh-dashboard .targets-card__body {
            padding: 1rem 1.75rem 1.75rem;
        }

        .tahfizh-dashboard .dropdown .menu {
            border-radius: 1rem;
            border: 1px solid var(--filament-card-border);
        }

        .tahfizh-dashboard table {
            border-spacing: 0;
        }

        .tahfizh-dashboard table thead th {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--filament-text-muted);
        }

        .tahfizh-dashboard table tbody tr td {
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        @media (max-width: 768px) {
            .tahfizh-dashboard .filament-hero {
                padding: 1.5rem;
            }

            .tahfizh-dashboard .filament-hero__icon {
                width: 72px;
                height: 72px;
            }

            .tahfizh-dashboard .stat-card {
                border-left-width: 3px;
            }

            .tahfizh-dashboard .planner-shell {
                padding: 1.25rem;
            }

            .tahfizh-dashboard .planner-shell .section-eyebrow {
                letter-spacing: 0.3em;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('.tahfizh-dashboard');
            if (!wrapper) return;

            const applyTheme = () => {
                const isDark = document.documentElement.classList.contains('dark');
                wrapper.setAttribute('data-theme', isDark ? 'emerald-dark' : 'emerald');
            };

            applyTheme();

            const observer = new MutationObserver(applyTheme);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        });
    </script>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <script>
        const ctxT = document.getElementById('halaqohChart').getContext('2d');
        const halaqohChart = new Chart(ctxT, {
            type: 'bar',
            data: {
                labels: {!! json_encode($hafalanPerHalaqoh->keys()) !!},
                datasets: [{
                    label: 'Jumlah Hafalan per Halaqoh',
                    data: {!! json_encode($hafalanPerHalaqoh->values()) !!},
                    backgroundColor: 'rgba(147, 51, 234, 0.6)',
                    borderColor: 'rgba(147, 51, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    title: {
                        display: true,
                        text: 'Rekap Hafalan Setiap Halaqoh',
                        font: { size: 16 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        const timelineCanvas = document.getElementById('santriTimelineChart');
        const timelineEmptyState = document.getElementById('timeline-empty-state');
        const initialTimeline = {
            labels: {!! json_encode($santriTimeline['labels']) !!},
            datasets: {!! json_encode($santriTimeline['datasets']) !!}
        };

        if (timelineCanvas) {
            const datasets = initialTimeline.datasets && initialTimeline.datasets.length
                ? initialTimeline.datasets
                : [{
                    label: 'Santri',
                    data: [],
                    borderColor: '#4f46e5',
                    backgroundColor: '#4f46e5',
                    tension: 0.3,
                    fill: false,
                }];

            window.tahfizhTimelineChart = new Chart(timelineCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: initialTimeline.labels ?? [],
                    datasets,
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Akumulasi Ayat Setoran per Santri' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            if (!initialTimeline.datasets || !initialTimeline.datasets.length) {
                timelineCanvas.classList.add('hidden');
                timelineEmptyState?.classList.remove('hidden');
            }
        }

        const timelinePicker = document.getElementById('timeline-picker-select');
        if (timelinePicker) {
            const endpoint = timelinePicker.dataset.endpoint;
            timelinePicker.addEventListener('change', async (event) => {
                const santriId = event.target.value;
                const santriName = event.target.selectedOptions[0]?.textContent.split(' — ')[0] ?? 'Santri';
                if (!endpoint) return;
                try {
                    const response = await fetch(`${endpoint}?santri_id=${santriId}`);
                    if (!response.ok) throw new Error('Gagal memuat data');
                    const payload = await response.json();
                    updateTimelineChart(payload, santriName);
                } catch (error) {
                    console.error(error);
                }
            });
        }

        function updateTimelineChart(payload, fallbackLabel = 'Santri') {
            if (!window.tahfizhTimelineChart) return;

            const labels = payload.labels ?? [];
            const dataset = payload.dataset ?? { label: fallbackLabel, data: [] };

            window.tahfizhTimelineChart.data.labels = labels;
            window.tahfizhTimelineChart.data.datasets = [{
                label: dataset.label || fallbackLabel,
                data: dataset.data || [],
                borderColor: '#4f46e5',
                backgroundColor: '#4f46e5',
                tension: 0.3,
                fill: false,
            }];
            window.tahfizhTimelineChart.update();

            if (dataset.data && dataset.data.length) {
                timelineCanvas?.classList.remove('hidden');
                timelineEmptyState?.classList.add('hidden');
            } else {
                timelineCanvas?.classList.add('hidden');
                if (timelineEmptyState) {
                    timelineEmptyState.textContent = 'Santri ini belum memiliki setoran hafalan.';
                    timelineEmptyState.classList.remove('hidden');
                }
            }
        }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const progressData = @json($progressChart);
        const percentageSeries = @json($percentageSeries);
        const previewUrl = @json(route('filament.admin.pages.tahfizh-dashboard.preview-target'));

        const buildBarChart = (ctx, payload) => new Chart(ctx, {
            type: 'bar',
            data: {
                labels: (payload && payload.labels) || [],
                datasets: (payload && payload.datasets) || [{ label: 'Capaian (%)', data: [] }],
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } },
        });

        const applyBarSeries = (chart, payload) => {
            if (!chart) return;
            chart.data.labels = (payload && payload.labels) || [];
            chart.data.datasets = (payload && payload.datasets) || [{ label: 'Capaian (%)', data: [] }];
            chart.update();
        };

        const buildAnnualChart = (ctx, payload) => new Chart(ctx, {
            type: 'bar',
            data: {
                labels: (payload && payload.labels) || [],
                datasets: [{
                    label: 'Capaian (%)',
                    data: (payload && payload.values) || [],
                    backgroundColor: 'rgba(244,114,182,0.7)',
                    borderColor: 'rgba(244,114,182,1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }],
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } },
        });

        const applyAnnualSeries = (chart, payload) => {
            if (!chart) return;
            chart.data.labels = (payload && payload.labels) || [];
            chart.data.datasets[0].data = (payload && payload.values) || [];
            chart.update();
        };

        const getBarPayload = (config, key) => {
            if (!config || !config.series || !key) {
                return { labels: [], datasets: [{ label: 'Capaian (%)', data: [] }] };
            }
            return config.series[key] || { labels: [], datasets: [{ label: 'Capaian (%)', data: [] }] };
        };

        const progressCtx = document.getElementById('targetProgressChart')?.getContext('2d');
        if (progressCtx && progressData) {
            new Chart(progressCtx, {
                type: 'bar',
                data: {
                    labels: progressData.labels || [],
                    datasets: [
                        {
                            label: 'Target Ayat',
                            data: progressData.target || [],
                            backgroundColor: 'rgba(59,130,246,0.4)',
                            borderColor: 'rgba(59,130,246,1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Realisasi Ayat',
                            data: progressData.actual || [],
                            backgroundColor: 'rgba(16,185,129,0.6)',
                            borderColor: 'rgba(16,185,129,1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } },
                },
            });
        }

        const monthlyCtx = document.getElementById('monthlyPercentageChart')?.getContext('2d');
        const semesterCtx = document.getElementById('semesterPercentageChart')?.getContext('2d');
        const annualCtx = document.getElementById('annualPercentageChart')?.getContext('2d');

        const monthlyConfig = (percentageSeries && percentageSeries.monthly) || {};
        const semesterConfig = (percentageSeries && percentageSeries.semester) || {};
        const annualConfig = (percentageSeries && percentageSeries.annual) || {};

        const monthlyChart = monthlyCtx ? buildBarChart(monthlyCtx, getBarPayload(monthlyConfig, monthlyConfig.current_key)) : null;
        const semesterChart = semesterCtx ? buildBarChart(semesterCtx, getBarPayload(semesterConfig, semesterConfig.current_key)) : null;
        const annualChart = annualCtx ? buildAnnualChart(annualCtx, (annualConfig && annualConfig.current) || { labels: [], values: [] }) : null;

    const bindSeriesSelect = (selectId, chart, config) => {
        const selectEl = document.getElementById(selectId);
        if (!selectEl || !chart || !config.series) return;
        selectEl.addEventListener('change', () => {
            const key = selectEl.value;
            if (!key) return;
            applyBarSeries(chart, config.series[key]);
        });
    };

    const bindAnnualSelect = (selectId, chart) => {
        const selectEl = document.getElementById(selectId);
        if (!selectEl || !chart) return;
        selectEl.addEventListener('change', () => {
            const period = selectEl.value;
            const payload = annualConfig[period];
            if (!payload) return;
            applyAnnualSeries(chart, payload);
        });
    };

    bindSeriesSelect('monthlyPeriodSelect', monthlyChart, monthlyConfig);
    bindSeriesSelect('semesterPeriodSelect', semesterChart, semesterConfig);
    bindAnnualSelect('annualPeriodSelect', annualChart);

        const coverageWrapper = document.querySelector('[data-coverage-endpoint]');
        const coverageModal = document.getElementById('coverageDetailModal');
        const coverageTitle = document.getElementById('coverageDetailTitle');
        const coverageBody = document.getElementById('coverageDetailBody');

        if (coverageWrapper && coverageModal && coverageTitle && coverageBody) {
            const endpointTpl = coverageWrapper.dataset.coverageEndpoint;
            const fetchDetail = async (santriId) => {
                const url = endpointTpl.replace('__SANTRI__', encodeURIComponent(santriId));
                const response = await fetch(url);
                if (!response.ok) throw new Error('gagal');
                return response.json();
            };
            coverageWrapper.querySelectorAll('[data-coverage-detail]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const santriId = btn.dataset.coverageDetail;
                    const santriName = btn.dataset.santriName || 'Santri';
                    coverageTitle.textContent = `Detail Hafalan - ${santriName}`;
                    coverageBody.innerHTML = '<p class="text-sm text-slate-500">Memuat data...</p>';
                    coverageModal.showModal();
                    try {
                        const payload = await fetchDetail(santriId);
                        if (!payload.detail || !payload.detail.length) {
                            coverageBody.innerHTML = '<p class="text-sm text-slate-500">Belum ada detail hafalan.</p>';
                            return;
                        }
                        coverageBody.innerHTML = payload.detail.map(item => `
                            <div class="border border-slate-100 dark:border-slate-700 rounded-xl p-3 mb-2">
                                <div class="font-semibold text-slate-800 dark:text-slate-100">${item.surah}</div>
                                <div class="text-sm text-slate-500">Ayat ${item.ayat_start} - ${item.ayat_end}</div>
                                <div class="text-xs text-slate-400">Setor: ${item.tanggal_setor}</div>
                            </div>
                        `).join('');
                    } catch (error) {
                        coverageBody.innerHTML = '<p class="text-sm text-red-500">Gagal memuat detail.</p>';
                    }
                });
            });
        }

});
    </script>
@endpush
