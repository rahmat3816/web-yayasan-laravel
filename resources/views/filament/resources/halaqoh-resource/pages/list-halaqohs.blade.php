<x-filament-panels::page
    @class([
        'setoran-page',
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @php
        $currentUser = auth()->user();
        $unitName = $currentUser?->unit->nama_unit ?? 'Semua Unit';
        // Prioritas tampilan role: dahulukan Kabag Kesantrian, lalu Koor Tahfizh, lalu koordinator lama.
        $rolePriorities = [
            'kabag_kesantrian_putra',
            'kabag_kesantrian_putri',
            'koor_tahfizh_putra',
            'koor_tahfizh_putri',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
        ];
        $roleAliases = [
            'koordinator_tahfizh_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfizh_putri' => 'koor_tahfizh_putri',
            'koordinator_tahfiz_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfiz_putri' => 'koor_tahfizh_putri',
            'koordinator_tahfihz_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfihz_putri' => 'koor_tahfizh_putri',
        ];
        $userRoles = collect($currentUser?->roles?->pluck('name')->toArray() ?? [])
            ->merge($currentUser?->jabatans?->pluck('slug')->toArray() ?? [])
            ->map(fn ($r) => strtolower($r))
            ->map(fn ($r) => $roleAliases[$r] ?? $r)
            ->unique()
            ->values();
        $primaryRole = collect($rolePriorities)->first(fn ($r) => $userRoles->contains($r));
        $roleName = $primaryRole ? strtoupper($primaryRole) : '-';
        $unassigned = max($this->totalHalaqoh - $this->totalPengampu, 0);
    @endphp

    <div class="setoran-wrapper space-y-10">
        <x-filament-panels::resources.tabs />

        <section class="setoran-hero">
            <div class="setoran-hero__content">
                <p class="setoran-eyebrow">Halaqoh Tahfizh</p>
                <h1 class="setoran-hero__title">Daftar Halaqoh & Pengampu</h1>
                <p class="setoran-hero__subtitle">
                    Pantau distribusi halaqoh, jumlah santri, dan penugasan guru pengampu dengan tampilan yang konsisten di seluruh panel tahfizh.
                </p>
                <div class="setoran-pill-row flex flex-wrap gap-3 text-sm font-semibold mt-4">
                    <span class="setoran-pill">Unit: {{ $unitName }}</span>
                    <span class="setoran-pill">Pengguna: {{ $currentUser?->name ?? '-' }}</span>
                    <span class="setoran-pill">Role: {{ $roleName }}</span>
                </div>
            </div>
            <div class="setoran-hero__actions">
                <a href="{{ $this->getResource()::getUrl('create') }}" class="setoran-button setoran-button--action w-full justify-center">
                    + Tambah Halaqoh
                </a>
            </div>
        </section>

        <section class="setoran-stats grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
            <article class="setoran-stat-card setoran-stat-card--neutral">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Total Halaqoh</p>
                    <p class="setoran-stat-value">{{ number_format($this->totalHalaqoh) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-rectangle-group" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Total Santri</p>
                    <p class="setoran-stat-value">{{ number_format($this->totalSantri) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-user-group" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--secondary">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Guru Pengampu</p>
                    <p class="setoran-stat-value">{{ number_format($this->totalPengampu) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-academic-cap" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--accent">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Belum Ada Pengampu</p>
                    <p class="setoran-stat-value">{{ number_format(max($unassigned, 0)) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-exclamation-circle" class="h-8 w-8 text-white/80" />
            </article>
        </section>

        <section class="setoran-card rekap-table">
            <div class="rekap-table__header">
                <div>
                    <h2 class="text-xl font-semibold">Tabel Halaqoh</h2>
                    <p class="text-sm">Gunakan filter unit, pencarian, atau aksi lainnya langsung dari tabel ini.</p>
                </div>
            </div>

            <div class="rekap-table__content rekap-table__content--filament">
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

                <div class="setoran-table-wrapper">
                    {{ $this->table }}
                </div>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            .fi-resource-halaqohs .fi-header {
                display: none !important;
            }

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
                padding: clamp(1.5rem, 3vw, 2rem) clamp(1rem, 3vw, 1.75rem) 3rem;
                background: var(--setoran-page-bg);
                overflow-x: hidden;
                width: 100%;
                box-sizing: border-box;
            }

            .setoran-wrapper {
                position: relative;
                max-width: 1200px;
                margin: 0 auto;
                width: 100%;
                box-sizing: border-box;
                color: var(--setoran-text);
            }

            .setoran-hero {
                position: relative;
                border-radius: 2rem;
                padding: clamp(1.75rem, 3vw, 2.5rem);
                background: var(--setoran-hero-gradient);
                color: #fff;
                box-shadow: 0 35px 80px rgba(15,23,42,0.35);
                display: flex;
                flex-wrap: wrap;
                gap: 2rem;
                min-width: 0;
            }

            .setoran-hero__content {
                flex: 1 1 320px;
                min-width: 0;
            }

            .setoran-eyebrow {
                font-size: 0.72rem;
                text-transform: uppercase;
                letter-spacing: 0.4em;
                color: rgba(255,255,255,0.85);
                font-weight: 600;
            }

            .setoran-hero__title {
                font-size: clamp(1.75rem, 3vw, 2.6rem);
                font-weight: 700;
                margin-top: 0.4rem;
                margin-bottom: 0.8rem;
            }

            .setoran-hero__subtitle {
                color: rgba(255,255,255,0.85);
                max-width: 520px;
                line-height: 1.5;
            }

            .setoran-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.4rem 1.3rem;
                border-radius: 999px;
                border: 1px solid var(--setoran-pill-border);
                background: rgba(255,255,255,0.15);
            }

            .setoran-hero__actions {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                min-width: 210px;
                width: min(240px, 100%);
            }

            .setoran-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 0.65rem 1.5rem;
                font-weight: 600;
                border: none;
                background: #fff;
                color: #0f172a;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
                box-shadow: 0 15px 35px rgba(15,23,42,0.25);
                text-decoration: none;
            }

            .setoran-button:hover {
                transform: translateY(-1px);
                box-shadow: 0 25px 45px rgba(15,23,42,0.3);
            }

            html.dark .setoran-button {
                background: rgba(255,255,255,0.92);
            }

            .setoran-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                margin-top: 1rem;
                margin-bottom: 2.75rem;
                gap: 1.25rem;
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
                min-width: 0;
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

            .setoran-stat-card--neutral {
                background: linear-gradient(135deg, #0ea5e9, #14b8a6);
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
            }

            .setoran-stat-value {
                font-size: 2.5rem;
                font-weight: 700;
                margin-top: 0.35rem;
            }

            .setoran-card {
                background: var(--setoran-card-bg);
                border: 1px solid var(--setoran-card-border);
                border-radius: 1.6rem;
                padding: 1.75rem;
                box-shadow: 0 25px 60px rgba(15,23,42,0.08);
                color: var(--setoran-text);
                min-width: 0;
            }

            .setoran-card p {
                color: var(--setoran-muted);
            }

            .rekap-table__content {
                border-radius: 1.25rem;
                overflow: hidden;
                border: 1px solid rgba(148, 163, 184, 0.2);
                margin-top: 1.5rem;
            }

            html.dark .rekap-table__content {
                border-color: rgba(148,163,184,0.35);
            }

            .setoran-table-wrapper {
                width: 100%;
                overflow-x: auto;
            }

            .setoran-table-wrapper .fi-ta-table {
                width: 100%;
                min-width: 580px;
            }

            .setoran-table-wrapper .fi-ta-table thead tr {
                background: transparent !important;
            }

            .setoran-table-wrapper .fi-ta-table thead th {
                font-size: 0.65rem;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                padding: 0.55rem 0.75rem;
                color: var(--setoran-muted);
                border-bottom: 1px solid rgba(148,163,184,0.25);
                background: transparent !important;
            }

            .setoran-table-wrapper .fi-ta-table tbody tr:hover {
                background-color: rgba(148, 163, 184, 0.12);
            }

            html.dark .setoran-table-wrapper .fi-ta-table tbody tr:hover {
                background-color: rgba(56, 189, 248, 0.08);
            }

            .setoran-table-wrapper .fi-ta-table tbody td {
                padding: 0;
                border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            }

            .setoran-table-wrapper .fi-ta-table tbody td .fi-ta-text {
                padding: 0.55rem 0.75rem;
            }

            .setoran-table-wrapper .fi-ta-table tbody td .fi-ta-text,
            .setoran-table-wrapper .fi-ta-table tbody td .fi-ta-text > * {
                width: 100%;
            }

            @media (max-width: 768px) {
                .setoran-hero {
                    padding: 1.25rem;
                    flex-direction: column;
                }

                .setoran-hero__actions {
                    width: 100%;
                }

                .setoran-hero__actions .setoran-button {
                    width: 100%;
                    justify-content: center;
                }

                .setoran-card {
                    padding: 1.25rem;
                    border-radius: 1.25rem;
                }

                .setoran-stats {
                    grid-template-columns: 1fr;
                    margin-bottom: 2rem;
                }

                .setoran-table-wrapper .fi-ta-table {
                    min-width: 100%;
                }
            }

            @media (max-width: 640px) {
                .setoran-page {
                    padding: 1rem;
                }

                .setoran-wrapper {
                    margin: 0;
                }

                .setoran-hero {
                    padding: 1rem;
                }

                .setoran-hero__actions {
                    min-width: 0;
                    width: 100%;
                }

                .setoran-pill-row {
                    width: 100%;
                }

                .setoran-pill {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>
