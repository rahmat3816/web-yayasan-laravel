@php use Illuminate\Support\Str; @endphp

@php
    $filters = $filters ?? [];
    $stats = $stats ?? [];
    $records = $records ?? [];
    $kitabSummary = $kitabSummary ?? [];
    $unitLabel = $unitId ? ($filters['units'][$unitId] ?? 'Unit Tidak Diketahui') : 'Semua Unit';
@endphp

<x-filament::page class="setoran-page">
    <div class="setoran-wrapper space-y-10">
        <section class="setoran-hero">
            <div class="setoran-hero__content w-full">
                <div class="space-y-4">
                    <p class="setoran-eyebrow">Tahfizh Mutun</p>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight">Rekap Target &amp; Setoran Mutun</h1>
                    <p class="text-white/80 max-w-2xl">
                        Pantau perkembangan target hafalan mutun berdasarkan unit, santri, dan kitab dengan tampilan yang konsisten dengan panel tahfizh Qur'an.
                    </p>
                    <div class="flex flex-wrap gap-3 text-sm font-semibold">
                        <span class="setoran-pill">Unit: {{ $unitLabel }}</span>
                        <span class="setoran-pill">Tahun: {{ $tahun ? $tahun : 'Semua Tahun' }}</span>
                        <span class="setoran-pill">Total Target: {{ number_format($stats['total_targets'] ?? 0) }}</span>
                    </div>
                </div>

                <div class="setoran-hero__form">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="text-xs uppercase tracking-wide text-white/80 font-semibold flex flex-col gap-2">
                            <span>Unit</span>
                            <select wire:model.live="unitId" class="setoran-select">
                                <option value="">Semua Unit</option>
                                @foreach ($filters['units'] ?? [] as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs uppercase tracking-wide text-white/80 font-semibold flex flex-col gap-2">
                            <span>Pilihan Kitab Mutun</span>
                            <select wire:model.live="kitab" class="setoran-select">
                                <option value="">Semua Kitab</option>
                                @foreach ($filters['kitab'] ?? [] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs uppercase tracking-wide text-white/80 font-semibold flex flex-col gap-2">
                            <span>Tahun</span>
                            <select wire:model.live="tahun" class="setoran-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($filters['years'] ?? [] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs uppercase tracking-wide text-white/80 font-semibold flex flex-col gap-2">
                            <span>Semester</span>
                            <select wire:model.live="semester" class="setoran-select">
                                <option value="">Semua Semester</option>
                                @foreach ($filters['semesters'] ?? [] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs uppercase tracking-wide text-white/80 font-semibold flex flex-col gap-2 md:col-span-2">
                            <span>Santri</span>
                            <select wire:model.live="santriId" class="setoran-select">
                                <option value="">Semua Santri</option>
                                @foreach ($filters['santri'] ?? [] as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <section class="setoran-stats">
            <article class="setoran-stat-card setoran-stat-card--neutral">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Total Target</p>
                    <p class="setoran-stat-value">{{ number_format($stats['total_targets'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Setoran Selesai</p>
                    <p class="setoran-stat-value">{{ number_format($stats['selesai'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-clipboard-document" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--accent">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Persentase Capaian</p>
                    <p class="setoran-stat-value">{{ number_format($stats['capaian'] ?? 0, 1) }}%</p>
                </div>
                <x-filament::icon icon="heroicon-o-sparkles" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--mutqin">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Rata-rata Mutqin</p>
                    <p class="setoran-stat-value">{{ number_format($stats['rata_mutqin'] ?? 0, 1) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-star" class="h-8 w-8 text-white/80" />
            </article>
        </section>

        <section class="setoran-card text-slate-900 dark:text-white">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Ringkasan Kitab</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-300">Perbandingan target mutun vs setoran per kitab.</p>
                </div>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @forelse ($kitabSummary as $kitab => $summary)
                    <article class="kitab-card">
                        <div class="kitab-card__header">
                            <div>
                                <p class="kitab-card__label">Kitab</p>
                                <h3 class="kitab-card__name">{{ $kitab }}</h3>
                            </div>
                            <span class="kitab-card__badge">{{ number_format($summary['avg_progress'] ?? 0, 1) }}%</span>
                        </div>
                        <div class="kitab-card__meta">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">Target</p>
                                <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ number_format($summary['total'] ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">Setoran</p>
                                <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ number_format($summary['completed'] ?? 0) }}</p>
                            </div>
                        </div>
                        <div class="kitab-card__progress">
                            <div class="kitab-card__progress-bar" style="width: {{ min(100, max(0, $summary['avg_progress'] ?? 0)) }}%"></div>
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-2 text-center text-slate-500 dark:text-slate-400">
                        Belum ada ringkasan kitab untuk filter yang dipilih.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="setoran-card text-slate-900 dark:text-white">
            <div class="rekap-table__header">
                <div>
                    <h2 class="text-lg font-semibold">Riwayat Setoran Mutun</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-300">Daftar mutun yang sudah disetorkan sesuai filter.</p>
                </div>
            </div>
            <div class="rekap-table__content overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Unit</th>
                            <th>Kitab &amp; Mutun</th>
                            <th>Penilai</th>
                            <th>Nilai Mutqin</th>
                            <th>Tanggal Setor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>
                                    <div class="font-semibold text-sm">{{ $record['santri'] }}</div>
                                    <p class="text-xs text-slate-500">Tahun {{ $record['tahun'] }} • {{ Str::headline($record['semester'] ?? '-') }}</p>
                                </td>
                                <td class="text-sm text-slate-500">{{ $record['unit'] }}</td>
                                <td>
                                    <div class="font-semibold text-sm text-slate-800 dark:text-white">
                                        Mutun {{ $record['nomor'] ?? '-' }} • {{ $record['mutun'] }}
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-300">{{ $record['kitab'] }}</p>
                                </td>
                                <td class="text-sm text-slate-600 dark:text-slate-300">{{ $record['penilai'] }}</td>
                                <td class="text-lg font-semibold text-slate-800 dark:text-white">{{ $record['mutqin'] ?? '-' }}</td>
                                <td class="text-sm text-slate-600 dark:text-slate-300">{{ $record['tanggal'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="rekap-table__empty">
                                    Belum ada data setoran mutun untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            :root {
                --setoran-page-bg: linear-gradient(180deg, #f8fafc 0%, #eef2ff 55%, #f0fdfa 100%);
                --setoran-card-bg: rgba(255,255,255,0.95);
                --setoran-card-border: rgba(15,23,42,0.08);
                --setoran-text: #0f172a;
                --setoran-muted: #64748b;
                --setoran-hero-gradient: linear-gradient(135deg, #1d4ed8, #0ea5e9 55%, #14b8a6);
                --setoran-pill-border: rgba(255,255,255,0.5);
            }

            html.dark {
                --setoran-page-bg: radial-gradient(circle at 10% 20%, rgba(15,118,210,0.3), rgba(2,6,23,0.95));
                --setoran-card-bg: rgba(2,6,23,0.85);
                --setoran-card-border: rgba(148,163,184,0.35);
                --setoran-text: #e2e8f0;
                --setoran-muted: rgba(226,232,240,0.65);
                --setoran-hero-gradient: linear-gradient(140deg, #0f172a, #1d4ed8 45%, #0ea5e9);
                --setoran-pill-border: rgba(255,255,255,0.25);
            }

            .setoran-page {
                position: relative;
                min-height: 100vh;
                padding: 1rem clamp(0.75rem, 4vw, 1.5rem) 3rem;
                background: var(--setoran-page-bg);
                overflow-x: hidden;
            }

            .setoran-wrapper {
                position: relative;
                max-width: min(1200px, 100%);
                margin: 0 auto;
                color: var(--setoran-text);
            }

            .setoran-wrapper > * + * {
                margin-top: clamp(1.75rem, 4vw, 2.75rem);
            }

            .setoran-hero {
                position: relative;
                border-radius: 2rem;
                padding: 2.5rem;
                color: #fff;
                background: var(--setoran-hero-gradient);
                overflow: hidden;
                box-shadow: 0 35px 80px rgba(15,23,42,0.35);
            }

            .setoran-hero::after,
            .setoran-hero::before {
                content: '';
                position: absolute;
                inset: 0;
                pointer-events: none;
            }

            .setoran-hero::before {
                background: radial-gradient(circle at top left, rgba(255,255,255,0.55), transparent 60%);
                opacity: 0.35;
            }

            .setoran-hero::after {
                background: radial-gradient(circle at bottom right, rgba(255,255,255,0.2), transparent 65%);
                opacity: 0.25;
            }

            .setoran-hero__content {
                position: relative;
                z-index: 2;
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            @media (min-width: 1024px) {
                .setoran-hero__content {
                    flex-direction: row;
                    align-items: flex-start;
                    justify-content: space-between;
                }
            }

            .setoran-hero__form {
                background: rgba(15,23,42,0.2);
                border-radius: 1.5rem;
                padding: 1.5rem;
                min-width: 260px;
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255,255,255,0.35);
                color: #fff;
            }

            .setoran-eyebrow {
                text-transform: uppercase;
                letter-spacing: 0.4em;
                font-size: 0.75rem;
                color: rgba(255,255,255,0.8);
                font-weight: 600;
            }

            .setoran-pill {
                padding: 0.5rem 1.2rem;
                border-radius: 999px;
                border: 1px solid var(--setoran-pill-border);
                background: rgba(255,255,255,0.15);
                color: #fff;
            }

            .setoran-select {
                width: 100%;
                border-radius: 999px;
                padding: 0.65rem 1rem;
                border: 1px solid rgba(255,255,255,0.35);
                background: rgba(255,255,255,0.9);
                color: #0f172a;
                font-weight: 600;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                background-image: none;
                padding-right: 1.5rem;
            }

            html.dark .setoran-select {
                background: rgba(15,23,42,0.85);
                border-color: rgba(148,163,184,0.35);
                color: #e2e8f0;
            }

            .setoran-select::-ms-expand {
                display: none;
            }

            .setoran-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: clamp(1.15rem, 2vw, 1.75rem);
                margin: 1.5rem 0;
            }

            .setoran-stat-card {
                position: relative;
                border-radius: 1.75rem;
                padding: 1.75rem;
                color: #fff;
                overflow: hidden;
                box-shadow: 0 25px 60px rgba(15,23,42,0.18);
                border: 1px solid rgba(255,255,255,0.2);
                display: flex;
                align-items: stretch;
                justify-content: space-between;
                gap: 1rem;
            }

            .setoran-stat-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(155deg, rgba(15,23,42,0.35), transparent 65%);
                z-index: 0;
            }

            .setoran-stat-card::after {
                content: '';
                position: absolute;
                inset: 0;
                opacity: 0.2;
                background: radial-gradient(circle at top right, rgba(255,255,255,0.8), transparent 55%);
                z-index: 0;
            }

            .setoran-stat-card > * {
                position: relative;
                z-index: 1;
            }

            .setoran-stat-card__content {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                flex: 1;
                min-height: 110px;
            }

            .setoran-stat-label {
                font-size: 0.75rem;
                letter-spacing: 0.25em;
                text-transform: uppercase;
                opacity: 0.95;
            }

            .setoran-stat-value {
                font-size: 2.5rem;
                font-weight: 700;
                margin-top: 0.35rem;
            }

            .setoran-stat-inline {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                margin-top: 0.35rem;
            }

            .setoran-stat-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.9rem;
                border-radius: 999px;
                background: rgba(255,255,255,0.2);
                font-weight: 600;
                letter-spacing: 0.15em;
            }

            .setoran-stat-card--neutral {
                background: linear-gradient(135deg, #475569, #1e293b);
            }

            .setoran-stat-card--accent {
                background: linear-gradient(135deg, #34d399, #059669);
            }

            .setoran-stat-card--mutqin {
                background: linear-gradient(135deg, #fb923c, #f97316);
            }

            .setoran-card {
                border-radius: 1.75rem;
                border: 1px solid var(--setoran-card-border);
                background: var(--setoran-card-bg);
                padding: 1.75rem;
                box-shadow: 0 25px 60px rgba(15,23,42,0.08);
                color: var(--setoran-text);
            }

            .kitab-card {
                border-radius: 1.25rem;
                border: 1px solid rgba(148,163,184,0.25);
                padding: 1.25rem;
                background: rgba(255,255,255,0.96);
            }

            html.dark .kitab-card {
                background: rgba(15,23,42,0.8);
                border-color: rgba(148,163,184,0.35);
            }

            .kitab-card__label {
                font-size: 0.7rem;
                letter-spacing: 0.3em;
                color: var(--setoran-muted);
                text-transform: uppercase;
            }

            .kitab-card__name {
                font-size: 1.1rem;
                font-weight: 700;
            }

            .kitab-card__badge {
                padding: 0.35rem 1rem;
                border-radius: 999px;
                font-size: 0.85rem;
                font-weight: 700;
                background: rgba(14,165,233,0.15);
                color: #0369a1;
            }

            .kitab-card__meta {
                display: flex;
                gap: 1.5rem;
                font-size: 0.85rem;
                color: var(--setoran-muted);
            }

            .kitab-card__progress {
                width: 100%;
                height: 0.4rem;
                border-radius: 999px;
                background: rgba(148,163,184,0.2);
                overflow: hidden;
            }

            .kitab-card__progress-bar {
                height: 100%;
                background: linear-gradient(135deg, #0ea5e9, #7c3aed);
            }

            .rekap-table__header {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }

            @media (min-width: 768px) {
                .rekap-table__header {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }

            .rekap-table__content table {
                width: 100%;
                border-collapse: collapse;
            }

            .rekap-table__content thead th {
                text-align: left;
                font-size: 0.75rem;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: var(--setoran-muted);
                padding: 0.75rem 1rem;
                border-bottom: 1px solid var(--setoran-card-border);
            }

            .rekap-table__content tbody td {
                padding: 0.9rem 1rem;
                border-bottom: 1px solid rgba(148,163,184,0.2);
            }

            .rekap-table__empty {
                text-align: center;
                padding: 1.5rem;
                color: var(--setoran-muted);
            }
        </style>
    @endpush
</x-filament::page>
