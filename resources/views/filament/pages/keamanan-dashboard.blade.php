<x-filament::page class="setoran-page">
    <div class="setoran-wrapper space-y-10">
        <section class="setoran-hero rekap-hero">
            <div class="setoran-hero__content w-full">
                <div class="space-y-3">
                    <p class="setoran-eyebrow">Keamanan</p>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">Dashboard Pelanggaran Santri</h1>
                    <p class="text-white/85 max-w-2xl">
                        Pantau jenis pelanggaran, status SP, dan catatan terbaru dengan tampilan konsisten dengan panel tahfizh.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('filament.admin.pages.keamanan.catat-pelanggaran') }}" class="setoran-primary-btn">Catat Pelanggaran</a>
                        <a href="{{ route('filament.admin.pages.keamanan.rekap-pelanggaran') }}" class="setoran-secondary-btn">Lihat Rekap</a>
                    </div>
                </div>
            </div>
            <div class="hero-legend">
                <div class="legend-item">
                    <span class="dot dot-sp1"></span>
                    <div>
                        <p class="font-semibold text-white">SP1</p>
                        <p class="text-xs text-white/70">Ambang 100 poin</p>
                    </div>
                </div>
                <div class="legend-item">
                    <span class="dot dot-sp2"></span>
                    <div>
                        <p class="font-semibold text-white">SP2</p>
                        <p class="text-xs text-white/70">Ambang 200 poin</p>
                    </div>
                </div>
                <div class="legend-item">
                    <span class="dot dot-sp3"></span>
                    <div>
                        <p class="font-semibold text-white">SP3</p>
                        <p class="text-xs text-white/70">Ambang 300 poin / langsung berat</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="setoran-stats grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <article class="setoran-stat-card setoran-stat-card--neutral">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Jenis Pelanggaran</p>
                    <p class="setoran-stat-value">{{ number_format($stats['jenis_pelanggaran'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--success">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">Log Pelanggaran</p>
                    <p class="setoran-stat-value">{{ number_format($stats['total_log'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-clipboard-document" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--accent">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">SP1</p>
                    <p class="setoran-stat-value">{{ number_format($stats['sp1'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-exclamation-circle" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--warning">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">SP2</p>
                    <p class="setoran-stat-value">{{ number_format($stats['sp2'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-bolt" class="h-8 w-8 text-white/80" />
            </article>
            <article class="setoran-stat-card setoran-stat-card--danger">
                <div class="setoran-stat-card__content">
                    <p class="setoran-stat-label">SP3</p>
                    <p class="setoran-stat-value">{{ number_format($stats['sp3'] ?? 0) }}</p>
                </div>
                <x-filament::icon icon="heroicon-o-shield-exclamation" class="h-8 w-8 text-white/80" />
            </article>
        </section>

        <section class="card grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="col-span-2">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Cepat</h3>
                </div>
                <div class="quick-grid">
                    <div class="quick-item">
                        <p class="text-sm text-white/70">Poin pelanggaran tercatat</p>
                        <p class="text-2xl font-semibold text-white">{{ number_format($stats['total_poin_pelanggaran'] ?? 0) }}</p>
                    </div>
                    <div class="quick-item">
                        <p class="text-sm text-white/70">Poin penghargaan/ketaatan</p>
                        <p class="text-2xl font-semibold text-white">{{ number_format($stats['total_poin_penghargaan'] ?? 0) }}</p>
                    </div>
                    <div class="quick-item">
                        <p class="text-sm text-white/70">Poin bersih</p>
                        <p class="text-2xl font-semibold text-white">{{ number_format(($stats['total_poin_pelanggaran'] ?? 0) - ($stats['total_poin_penghargaan'] ?? 0)) }}</p>
                    </div>
                </div>
            </div>
            <div class="card-cta">
                <p class="text-white font-semibold mb-1">Butuh pengurangan poin?</p>
                <p class="text-sm text-white/80 mb-3">Alokasikan ketaatan (reward) kepada santri dengan poin ≥ 200.</p>
                <a href="{{ route('filament.admin.pages.keamanan.rekap-pelanggaran') }}" class="setoran-secondary-btn w-full text-center">Daftar Ketaatan</a>
            </div>
        </section>

        <section class="setoran-card text-slate-900 dark:text-white">
            <div class="rekap-table__header">
                <div>
                    <h2 class="text-lg font-semibold">Log Pelanggaran Terbaru</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-300">6 catatan terakhir.</p>
                </div>
            </div>
            <div class="rekap-table__content overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Pelanggaran</th>
                            <th>Kategori</th>
                            <th>Poin</th>
                            <th>SP</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLogs as $log)
                            <tr>
                                <td>{{ $log['santri'] }}</td>
                                <td>{{ $log['pelanggaran'] }}</td>
                                <td>{{ $log['kategori'] }}</td>
                                <td>{{ $log['poin'] }}</td>
                                <td>{{ $log['sp'] }}</td>
                                <td>{{ $log['tanggal'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="rekap-table__empty">
                                    Belum ada pelanggaran dicatat.
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
                --setoran-page-bg: linear-gradient(180deg, #0f172a 0%, #0b1733 55%, #0a1f3f 100%);
                --setoran-card-bg: rgba(255,255,255,0.95);
                --setoran-card-border: rgba(15,23,42,0.08);
                --setoran-text: #0f172a;
                --setoran-muted: #64748b;
                --setoran-hero-gradient: linear-gradient(135deg, #0f172a, #0ea5e9 45%, #22c55e);
                --setoran-pill-border: rgba(255,255,255,0.35);
            }

            html.dark {
                --setoran-page-bg: radial-gradient(circle at 10% 20%, rgba(14,165,233,0.2), rgba(2,6,23,0.95));
                --setoran-card-bg: rgba(2,6,23,0.85);
                --setoran-card-border: rgba(148,163,184,0.35);
                --setoran-text: #e2e8f0;
                --setoran-muted: rgba(226,232,240,0.65);
                --setoran-hero-gradient: linear-gradient(140deg, #020617, #0f172a 45%, #0ea5e9);
                --setoran-pill-border: rgba(255,255,255,0.25);
            }

            .setoran-stat-card--accent { background: linear-gradient(135deg, #06b6d4, #0ea5e9); }
            .setoran-stat-card--warning { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
            .setoran-stat-card--danger { background: linear-gradient(135deg, #f97316, #ef4444); }
            .rekap-hero {
                background: radial-gradient(120% 120% at 0% 0%, rgba(59,130,246,0.18), rgba(14,165,233,0.16)),
                            linear-gradient(120deg, #0b1225 0%, #112449 60%, #0b1225 100%);
            }
            .hero-legend {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 0.75rem;
                padding: 1rem;
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 1rem;
            }
            .legend-item { display: flex; gap: 0.6rem; align-items: center; }
            .dot { width: 12px; height: 12px; border-radius: 999px; display: inline-block; }
            .dot-sp1 { background: #22c55e; }
            .dot-sp2 { background: #f59e0b; }
            .dot-sp3 { background: #ef4444; }
            .card {
                background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 1.2rem;
                padding: 1rem 1.25rem;
            }
            .card-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.5rem; }
            .card-title { color: #fff; font-weight: 700; }
            .quick-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 0.8rem;
            }
            .quick-item {
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 1rem;
                padding: 0.9rem 1rem;
            }
            .card-cta {
                background: linear-gradient(135deg, #0ea5e9, #22c55e);
                border-radius: 1rem;
                padding: 1rem;
                border: 1px solid rgba(255,255,255,0.15);
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        </style>
    @endpush
    @include('filament.pages.partials.setoran-styles')
</x-filament::page>
