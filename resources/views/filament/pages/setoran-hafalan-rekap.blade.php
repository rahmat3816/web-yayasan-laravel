<x-filament::page class="setoran-page">
@php
    $halaqoh = $halaqoh ?? null;
    $rekap = $rekap ?? [];
    $data = $data ?? collect();
    $totalSetoran = $totalSetoran ?? $data->count();
    $santriOptions = $santriOptions ?? collect();
    $selectedSantriId = $selectedSantriId ?? 'all';
    $scoreSummary = $scoreSummary ?? [
        'tajwid' => ['average' => 0, 'grade' => '-'],
        'mutqin' => ['average' => 0, 'grade' => '-'],
        'adab' => ['average' => 0, 'grade' => '-'],
    ];
@endphp

<div class="setoran-wrapper space-y-10">
    <section class="setoran-hero">
        <div class="setoran-hero__content">
            <div class="space-y-4">
                <p class="setoran-eyebrow">Rekap Setoran</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight">Ringkasan Setoran Halaqoh</h1>
                <div class="flex flex-wrap gap-3 text-sm font-semibold">
                    <span class="setoran-pill">Halaqoh: {{ $halaqoh->nama_halaqoh ?? '-' }}</span>
                    <span class="setoran-pill">Guru: {{ $halaqoh->guru->nama ?? 'Belum ditentukan' }}</span>
                    <span class="setoran-pill">Unit: {{ $halaqoh->unit->nama_unit ?? '-' }}</span>
                </div>
                <form method="GET" class="santri-filter-form space-y-2 sm:space-y-0 sm:flex sm:items-center sm:gap-3">
                    @foreach(request()->except('santri_id') as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <label for="santri-filter-select" class="text-xs uppercase tracking-wide text-white/80 font-semibold">Filter Santri</label>
                    <select id="santri-filter-select" name="santri_id" class="setoran-select santri-filter-select sm:w-auto">
                        <option value="all" @selected($selectedSantriId === 'all')>Semua Santri</option>
                        @foreach ($santriOptions as $option)
                            <option value="{{ $option->id }}" @selected((string) $selectedSantriId === (string) $option->id)>
                                {{ $option->nama }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="setoran-hero__actions"></div>
        </div>
    </section>

    <section class="setoran-stats grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
        <article class="setoran-stat-card setoran-stat-card--neutral">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Halaman</p>
                <p class="setoran-stat-value">{{ $rekap['total_halaman'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Juz</p>
                <p class="setoran-stat-value">{{ $rekap['total_juz'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-clock" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card setoran-stat-card--secondary">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Surah</p>
                <p class="setoran-stat-value">{{ $rekap['total_surah'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-book-open" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card setoran-stat-card--accent">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Jumlah Setoran</p>
                <p class="setoran-stat-value">{{ $totalSetoran }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-8 w-8 text-white/80" />
        </article>
    </section>

    <section class="setoran-stats grid gap-4 grid-cols-1 sm:grid-cols-3 xl:grid-cols-3">
        <article class="setoran-stat-card setoran-grade-card grade--tajwid">
            <div class="setoran-grade-card__content">
                <p class="setoran-stat-label">Rata-rata Tajwid</p>
                <div class="setoran-grade-card__value">
                    <span class="grade-score">{{ number_format($scoreSummary['tajwid']['average'], 1) }}</span>
                    <span class="grade-letter">{{ $scoreSummary['tajwid']['grade'] }}</span>
                </div>
            </div>
        </article>
        <article class="setoran-stat-card setoran-grade-card grade--mutqin">
            <div class="setoran-grade-card__content">
                <p class="setoran-stat-label">Rata-rata Mutqin</p>
                <div class="setoran-grade-card__value">
                    <span class="grade-score">{{ number_format($scoreSummary['mutqin']['average'], 1) }}</span>
                    <span class="grade-letter">{{ $scoreSummary['mutqin']['grade'] }}</span>
                </div>
            </div>
        </article>
        <article class="setoran-stat-card setoran-grade-card grade--adab">
            <div class="setoran-grade-card__content">
                <p class="setoran-stat-label">Rata-rata Adab</p>
                <div class="setoran-grade-card__value">
                    <span class="grade-score">{{ number_format($scoreSummary['adab']['average'], 1) }}</span>
                    <span class="grade-letter">{{ $scoreSummary['adab']['grade'] }}</span>
                </div>
            </div>
        </article>
    </section>

    <section class="setoran-card rekap-table">
        <div class="rekap-table__header">
            <div>
                <h2 class="text-xl font-semibold">Detail Setoran</h2>
                <p class="text-sm">Semua catatan disajikan apa adanya dari log penilaian.</p>
            </div>
        </div>

        <div class="rekap-table__content">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Santri</th>
                            <th>Surah</th>
                            <th>Rentang Ayat</th>
                            <th>Juz</th>
                            <th>Tajwid</th>
                            <th>Mutqin</th>
                            <th>Adab</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $h)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($h->tanggal_setor)->translatedFormat('d M Y') }}</td>
                                <td>{{ $h->santri->nama ?? '-' }}</td>
                                <td>{{ $h->surah_id ? sprintf('%03d', $h->surah_id) : '-' }}</td>
                                <td>Ayat {{ $h->ayah_start }}-{{ $h->ayah_end }}</td>
                                <td>{{ $h->juz_start }}</td>
                                <td>{{ $h->penilaian_tajwid ?? '-' }}</td>
                                <td>{{ $h->penilaian_mutqin ?? '-' }}</td>
                                <td>{{ $h->penilaian_adab ?? '-' }}</td>
                                <td>{{ $h->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="rekap-table__empty">
                                    Belum ada data setoran untuk halaqoh ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <div class="rekap-footer-controls">
        <a href="{{ route('filament.admin.pages.tahfizh.setoran-hafalan', array_filter(['halaqoh_id' => request('halaqoh_id')])) }}"
           class="setoran-secondary-btn">
            &larr; Kembali
        </a>
        <div class="per-page-form">
            <label for="per-page-select-bottom">Tampil</label>
            <select id="per-page-select-bottom" class="setoran-select per-page-select">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>
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
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .setoran-page {
            padding: 1rem;
        }
    }

    .setoran-hero {
        position: relative;
        border-radius: 2rem;
        padding: 2.5rem;
        color: #fff;
        background: var(--setoran-hero-gradient);
        overflow: hidden;
        box-shadow: 0 35px 80px rgba(15,23,42,0.35);
        margin-bottom: 2rem;
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

    .setoran-hero__actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
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

    .santri-filter-form {
        background: rgba(15,23,42,0.18);
        border-radius: 1rem;
        padding: 0.9rem 1.2rem;
        width: 100%;
        max-width: 380px;
        backdrop-filter: blur(6px);
    }

    .santri-filter-form label {
        color: rgba(255,255,255,0.85);
        font-size: 0.7rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    .santri-filter-select {
        background: rgba(255,255,255,0.95);
        color: #0f172a;
        font-size: 0.85rem;
        padding: 0.5rem 0.9rem;
    }

    html.dark .santri-filter-form {
        background: rgba(15,23,42,0.5);
    }

    html.dark .santri-filter-select {
        background: rgba(15,23,42,0.85);
        color: #e2e8f0;
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
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none !important;
        padding-right: 1.5rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .setoran-select::-ms-expand {
        display: none;
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
        margin-top: 0.5rem;
        margin-bottom: 1.5rem;
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

    .setoran-stat-card--neutral {
        background: linear-gradient(135deg, #0ea5e9, #14b8a6);
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

    .setoran-grade-card {
        position: relative;
        overflow: hidden;
    }

    .setoran-grade-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.4), transparent 55%);
        opacity: 0.35;
        pointer-events: none;
    }

    .setoran-grade-card__content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .setoran-grade-card__value {
        display: flex;
        align-items: baseline;
        gap: 0.75rem;
    }

    .setoran-grade-card__value .grade-score {
        font-size: 2.35rem;
        font-weight: 700;
    }

    .setoran-grade-card__value .grade-letter {
        font-size: 1.15rem;
        font-weight: 700;
        padding: 0.15rem 0.95rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.2);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.35);
    }

    .grade--tajwid {
        background: linear-gradient(135deg, #3b82f6, #a855f7);
    }

    .grade--mutqin {
        background: linear-gradient(135deg, #0ea5e9, #14b8a6);
    }

    .grade--adab {
        background: linear-gradient(135deg, #f97316, #fb7185);
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
        width: 100%;
        min-width: 0;
        overflow: hidden;
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

    .setoran-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border: 1px solid rgba(15,23,42,0.2);
        color: #0f172a;
        background-color: transparent;
    }

    html.dark .setoran-secondary-btn {
        border-color: rgba(148,163,184,0.4);
        color: #e2e8f0;
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

    .rekap-table h2,
    .rekap-table p,
    .rekap-table table {
        color: var(--setoran-text);
    }

    .rekap-table__header {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .rekap-table__header p {
        color: var(--setoran-muted);
    }

    @media (min-width: 768px) {
        .rekap-table__header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .setoran-hero__actions .setoran-secondary-btn {
        width: auto;
    }

    @media (max-width: 767px) {
        .setoran-hero__actions {
            width: 100%;
            align-items: stretch;
        }

        .setoran-hero__actions .setoran-secondary-btn {
            width: 100%;
            justify-content: center;
        }
    }

    .rekap-table__content table {
        border-collapse: collapse;
        width: 100%;
    }

    .rekap-table__content thead th {
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        padding: 0.75rem 1rem;
        color: var(--setoran-muted);
        border-bottom: 1px solid var(--setoran-card-border);
    }

    .rekap-table__content tbody td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid rgba(148,163,184,0.2);
    }

    .rekap-table__content tbody tr:hover {
        background: rgba(148,163,184,0.08);
    }

    .rekap-table__empty {
        text-align: center;
        padding: 1.5rem;
        color: var(--setoran-muted);
    }

    .rekap-footer-controls {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .rekap-footer-controls .setoran-secondary-btn {
        align-self: flex-start;
    }

    .rekap-footer-controls .per-page-form {
        align-self: center;
    }

    @media (min-width: 768px) {
        .rekap-footer-controls {
            flex-direction: row;
            align-items: center;
            justify-content: center;
            column-gap: 2rem;
        }

        .rekap-footer-controls .setoran-secondary-btn {
            margin-right: auto;
        }

        .rekap-footer-controls .per-page-form {
            margin-left: auto;
        }
    }

    .per-page-form {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
    }

    .per-page-form label {
        font-size: 0.85rem;
        color: var(--setoran-muted);
    }

    .per-page-select {
        width: auto;
        min-width: 120px;
    }

    .rekap-row-hidden {
        display: none;
    }

</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canUseAjaxFilter = typeof window.fetch === 'function' && typeof window.DOMParser === 'function';

    const applyLimit = () => {
        const perPageSelect = document.getElementById('per-page-select-bottom');
        if (!perPageSelect) {
            return;
        }

        const rows = Array.from(document.querySelectorAll('.rekap-table__content tbody tr'));
        const limit = perPageSelect.value === 'all' ? Number.POSITIVE_INFINITY : parseInt(perPageSelect.value, 10);
        let visibleCount = 0;

        rows.forEach((row) => {
            const isEmptyRow = row.querySelector('.rekap-table__empty') !== null;
            const shouldShow = isEmptyRow || visibleCount < limit;
            row.classList.toggle('rekap-row-hidden', !shouldShow);

            if (!isEmptyRow && shouldShow) {
                visibleCount++;
            }
        });
    };

    const fetchAndSwap = async (url) => {
        const previousPerPage = document.getElementById('per-page-select-bottom')?.value || null;
        const fetchUrl = new URL(url, window.location.origin);
        fetchUrl.searchParams.set('_ts', Date.now().toString());

        const response = await fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-store',
            },
            credentials: 'same-origin',
            cache: 'no-store',
        });

        if (!response.ok) {
            throw new Error('Failed to fetch filtered data');
        }

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const currentWrapper = document.querySelector('.setoran-wrapper');
        const incomingWrapper = doc.querySelector('.setoran-wrapper');

        if (currentWrapper && incomingWrapper) {
            currentWrapper.innerHTML = incomingWrapper.innerHTML;
        }

        if (previousPerPage) {
            const refreshedPerPage = document.getElementById('per-page-select-bottom');
            if (refreshedPerPage) {
                refreshedPerPage.value = previousPerPage;
            }
        }

        applyLimit();
    };

    const handleSantriChange = (select) => {
        if (!canUseAjaxFilter) {
            select.form?.submit();
            return;
        }

        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        const value = select.value;

        if (value === 'all') {
            params.delete('santri_id');
        } else {
            params.set('santri_id', value);
        }

        const query = params.toString();
        const url = `${currentUrl.pathname}${query ? `?${query}` : ''}`;

        select.disabled = true;

        fetchAndSwap(url)
            .then(() => {
                window.history.replaceState({}, '', url);
            })
            .catch(() => {
                window.location.href = url;
            })
            .finally(() => {
                const refreshedSelect = document.getElementById('santri-filter-select');
                if (refreshedSelect) {
                    refreshedSelect.disabled = false;
                }
            });
    };

    document.addEventListener('change', (event) => {
        if (event.target?.id === 'per-page-select-bottom') {
            applyLimit();
        }

        if (event.target?.id === 'santri-filter-select') {
            handleSantriChange(event.target);
        }
    });

    applyLimit();
});
</script>
@endpush
</x-filament::page>



