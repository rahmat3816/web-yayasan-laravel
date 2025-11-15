<x-filament::page class="setoran-page">
@php
    $rekap = $rekap ?? [];
    $santri = $santri ?? collect();
    $halaqoh = $halaqoh ?? null;
    $allHalaqoh = $allHalaqoh ?? collect();
    $isSuper = $isSuper ?? false;
@endphp

<div class="setoran-wrapper space-y-8">
    {{-- Hero --}}
    <section class="setoran-hero">
        <div class="setoran-hero__content">
            <div class="space-y-3">
                <p class="setoran-eyebrow">Halaqoh Qur'an</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight">Setoran Hafalan Quran</h1>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="setoran-pill">
                        Halaqoh: {{ $halaqoh->nama_halaqoh ?? '-' }}
                    </span>
                    <span class="setoran-pill">
                        Guru: {{ $halaqoh->guru->nama ?? 'Belum ditentukan' }}
                    </span>
                    <span class="setoran-pill">
                        Unit: {{ $halaqoh->unit->nama_unit ?? '-' }}
                    </span>
                </div>
            </div>
            @if ($isSuper && $allHalaqoh->count())
                <div class="setoran-hero__form">
                    <form method="GET" action="{{ route('filament.admin.pages.tahfizh.setoran-hafalan') }}" class="space-y-3">
                        <label class="text-xs font-semibold text-white tracking-wide uppercase">Pilih Halaqoh</label>
                        <select name="halaqoh_id" class="setoran-select w-full">
                            @foreach ($allHalaqoh as $h)
                                <option value="{{ $h->id }}" @selected(request('halaqoh_id', $halaqoh->id ?? 0) == $h->id)>
                                    H{{ $h->id }} - {{ $h->guru->nama ?? 'Belum Ada Guru' }} ({{ $h->unit->nama_unit ?? 'Unit '.$h->unit_id }})
                                </option>
                            @endforeach
                        </select>
                        <button class="setoran-button w-full" type="submit">Tampilkan</button>
                    </form>
                </div>
            @endif
        </div>
    </section>

    {{-- Stats --}}
    <section class="setoran-stats grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
        <article class="setoran-stat-card">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Halaman</p>
                <p class="setoran-stat-value">{{ $rekap['total_halaman'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card setoran-stat-card--secondary">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Juz</p>
                <p class="setoran-stat-value">{{ $rekap['total_juz'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-trophy" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card setoran-stat-card--accent">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Surah</p>
                <p class="setoran-stat-value">{{ $rekap['total_surah'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-book-open" class="h-8 w-8 text-white/80" />
        </article>
    </section>

    {{-- Santri cards --}}
    <section class="setoran-card text-slate-900 dark:text-white">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Santri dalam Halaqoh</h2>
            </div>
            <a href="{{ route('filament.admin.pages.tahfizh.setoran-hafalan.rekap', array_filter(['halaqoh_id' => request('halaqoh_id', $halaqoh->id ?? null)])) }}"
               class="setoran-link">
                <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5" />
                Lihat Rekap Detail
            </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($santri as $s)
                <article class="santri-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">Santri</p>
                            <h3 class="text-lg font-semibold">{{ $s->nama }}</h3>
                        </div>
                        <span class="santri-chip {{ $s->jenis_kelamin === 'P' ? 'chip-danger' : 'chip-warning' }}">
                            {{ $s->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-Laki' }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                        Detail progres lengkap tersedia pada menu Rekap Setoran.
                    </p>
                    <div class="mt-8">
                        @if (!$isSuper)
                            <a href="{{ route('filament.admin.pages.setoran-hafalan.create', ['santri' => $s->id]) }}" class="setoran-button setoran-button--action w-full justify-center">
                                + Setor Baru
                            </a>
                        @else
                            <span class="text-xs text-slate-500">Superadmin hanya mode pantau.</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-white dark:bg-slate-800/60 p-8 text-center text-sm text-slate-500">
                    Belum ada santri pada halaqoh ini.
                </div>
            @endforelse
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
            padding: 1rem 0 2.5rem;
            background: var(--setoran-page-bg);
            overflow-x: hidden;
        }

        .setoran-wrapper {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            color: var(--setoran-text);
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
                justify-content: space-between;
                align-items: flex-start;
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
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.4em;
            color: rgba(255,255,255,0.85);
            font-weight: 600;
        }

        .setoran-pill {
           	display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1.2rem;
            border-radius: 999px;
            border: 1px solid var(--setoran-pill-border);
            background: rgba(255,255,255,0.15);
            font-weight: 600;
        }

        .setoran-select {
            width: 100%;
            border-radius: 999px;
            padding: 0.65rem 1rem;
            border: 1px solid rgba(15,23,42,0.1);
            background: rgba(255,255,255,0.95);
            color: #0f172a;
            font-weight: 600;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .setoran-select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,0.25);
        }

        html.dark .setoran-select {
            background: rgba(15,23,42,0.85);
            border-color: rgba(148,163,184,0.35);
            color: #e2e8f0;
        }

        .setoran-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.65rem 1.25rem;
            border-radius: 999px;
            border: none;
            background: #fff;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .setoran-button--action {
            margin-top: 1.25rem;
        }

        .setoran-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(15,23,42,0.25);
        }

        html.dark .setoran-button {
            background: rgba(255,255,255,0.92);
            color: #0f172a;
        }

        .setoran-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .setoran-stats .setoran-stat-card {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            border-radius: 1.5rem;
            padding: 1.75rem;
            color: #fff;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(37,99,235,0.25);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.2);
            gap: 1rem;
        }

        .setoran-stats .setoran-stat-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(155deg, rgba(15,23,42,0.35), transparent 65%);
            z-index: 0;
        }

        .setoran-stats .setoran-stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.2;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.8), transparent 55%);
            z-index: 0;
        }

        .setoran-stats .setoran-stat-card > * {
            position: relative;
            z-index: 1;
        }

        .setoran-stat-card__content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            min-height: 100px;
        }

        .setoran-stat-card--secondary {
            background: linear-gradient(135deg, #7c3aed, #c084fc);
        }

        .setoran-stat-card--accent {
            background: linear-gradient(135deg, #fb923c, #facc15);
        }

        .setoran-stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            opacity: 0.95;
            text-shadow: 0 8px 25px rgba(15,23,42,0.4);
        }

        .setoran-stat-value {
            font-size: 2.75rem;
            font-weight: 700;
            margin-top: 0.4rem;
            text-shadow: 0 10px 30px rgba(15,23,42,0.45);
        }

        .setoran-card {
            background: var(--setoran-card-bg);
            border: 1px solid var(--setoran-card-border);
            border-radius: 1.6rem;
            padding: 1.75rem;
            box-shadow: 0 25px 60px rgba(15,23,42,0.08);
            backdrop-filter: blur(15px);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            color: var(--setoran-text);
        }

        .setoran-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 30px 65px rgba(15,23,42,0.12);
            border-color: rgba(14,165,233,0.35);
        }

        .setoran-card p {
            color: var(--setoran-muted);
        }

        .setoran-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            color: #2563eb;
        }

        html.dark .setoran-link {
            color: #38bdf8;
        }

        .santri-card {
            border: 1px solid var(--setoran-card-border);
            border-radius: 1.5rem;
            padding: 1.3rem;
            background: var(--setoran-card-bg);
            box-shadow: 0 15px 35px rgba(15,23,42,0.08);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            backdrop-filter: blur(8px);
            color: var(--setoran-text);
        }

        .santri-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px rgba(15,23,42,0.12);
            border-color: rgba(14,165,233,0.35);
        }

        .santri-card p {
            color: var(--setoran-muted);
        }

        .santri-chip {
            border-radius: 999px;
            padding: 0.25rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
        }

        .chip-danger {
            background: linear-gradient(135deg, #f43f5e, #fb7185);
        }

        .chip-warning {
            background: linear-gradient(135deg, #f97316, #facc15);
        }
    </style>
@endpush
</x-filament::page>
