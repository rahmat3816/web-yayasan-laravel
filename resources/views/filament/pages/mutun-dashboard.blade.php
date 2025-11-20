<x-filament::page class="mutun-dashboard-page">
    <section class="dashboard-hero">
        <div class="dashboard-hero__content">
            <p class="dashboard-eyebrow">Tahfizh Mutun</p>
            <h1 class="dashboard-title">Ringkasan Hafalan Mutun</h1>
            <p class="dashboard-subtitle">
                Pantau progres target, riwayat setoran, dan capaian hafalan mutun setiap santri.
            </p>
        </div>
        @if (!empty($santriOptions))
            <form method="GET" class="dashboard-filter">
                <label class="dashboard-filter__label">Pilih Santri</label>
                <select name="santri_id" class="dashboard-filter__select" onchange="this.form.submit()">
                    @foreach ($santriOptions as $option)
                        <option value="{{ $option['id'] }}" @selected($option['id'] === $selectedSantriId)>
                            {{ $option['nama'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </section>

    <section class="mutun-dashboard-stats">
        <article class="dashboard-card">
            <p class="dashboard-card__label">Target Terdaftar</p>
            <p class="dashboard-card__value">{{ number_format($stats['total_targets'] ?? 0) }}</p>
        </article>
        <article class="dashboard-card dashboard-card--success">
            <p class="dashboard-card__label">Setoran</p>
            <div class="dashboard-card__inline">
                <p class="dashboard-card__value">{{ number_format($stats['total_setorans'] ?? 0) }}</p>
                <span class="dashboard-card__badge">{{ number_format($stats['capaian'] ?? 0, 1) }}%</span>
            </div>
        </article>
        <article class="dashboard-card dashboard-card--mutqin">
            <p class="dashboard-card__label">Rata-rata Mutqin</p>
            <p class="dashboard-card__value">{{ number_format($stats['avg_mutqin'] ?? 0, 1) }}</p>
        </article>
    </section>

    @if (!empty($percentageSummary))
        <section class="dashboard-percentages">
            @foreach ($percentageSummary as $item)
                <article class="percentage-card">
                    <p class="percentage-card__title">{{ $item['title'] ?? '-' }}</p>
                    <div class="percentage-card__value">
                        <span class="percentage-card__number">{{ number_format($item['value'] ?? 0) }}</span>
                        <span class="percentage-card__unit">Mutun</span>
                    </div>
                    <p class="percentage-card__label">{{ $item['label'] ?? '-' }}</p>
                </article>
            @endforeach
        </section>
    @endif

    @if (!empty($lineCharts))
        <section class="dashboard-widget">
            <div class="dashboard-widget__header">
                <h2>Riwayat Setoran Mutun</h2>
                <p>Grafik garis per kitab untuk santri terpilih.</p>
            </div>
            <div class="dashboard-widget__body grid gap-6 md:grid-cols-2">
                @foreach ($lineCharts as $chart)
                    <div class="chart-card">
                        <div class="chart-card__header">
                            <h3>{{ $chart['kitab'] }}</h3>
                        </div>
                        <div class="chart-card__canvas">
                            <canvas id="{{ $chart['canvas_id'] }}"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (!empty($kitabAchievements))
        <section class="dashboard-widget">
            <div class="dashboard-widget__header">
                <h2>Capaian Target per Kitab</h2>
                <p>Total mutun vs setoran untuk masing-masing kitab.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Kitab</th>
                            <th>Total Mutun</th>
                            <th>Setoran</th>
                            <th>Capaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kitabAchievements as $item)
                            <tr>
                                <td>{{ $item['kitab'] }}</td>
                                <td>{{ number_format($item['total']) }}</td>
                                <td>{{ number_format($item['completed']) }}</td>
                                <td>{{ number_format($item['percentage'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="dashboard-widget">
        <div class="dashboard-widget__header">
            <h2>Setoran Terbaru</h2>
            <p>5 catatan setoran mutun terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Santri</th>
                        <th>Mutun</th>
                        <th>Penilai</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSetorans as $setoran)
                        <tr>
                            <td>{{ $setoran['tanggal'] ?? '-' }}</td>
                            <td>{{ $setoran['santri'] ?? '-' }}</td>
                            <td>
                                <div class="font-semibold">Mutun {{ $setoran['mutun_nomor'] ?? '-' }}</div>
                                <p class="text-xs text-slate-500">{{ $setoran['mutun'] ?? '-' }}</p>
                            </td>
                            <td>{{ $setoran['penilai'] ?? '-' }}</td>
                            <td>{{ $setoran['catatan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-500">Belum ada data setoran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-filament::page>

@push('styles')
    <style>
        .mutun-dashboard-page {
            --md-text: #0f172a;
            --md-muted: #475569;
            --md-card-bg: #ffffff;
            --md-border: rgba(15,23,42,0.08);
            --md-soft-bg: rgba(248,250,252,0.9);
            --md-hero-start: #1d4ed8;
            --md-hero-mid: #2563eb;
            --md-hero-end: #7c3aed;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: var(--md-text);
        }

        .dark .mutun-dashboard-page {
            --md-text: #e2e8f0;
            --md-muted: #cbd5f5;
            --md-card-bg: rgba(15,23,42,0.88);
            --md-border: rgba(148,163,184,0.35);
            --md-soft-bg: rgba(15,23,42,0.65);
            --md-hero-start: #1e1b4b;
            --md-hero-mid: #3730a3;
            --md-hero-end: #4c1d95;
        }

        .dashboard-hero {
            background: linear-gradient(135deg, var(--md-hero-start), var(--md-hero-mid), var(--md-hero-end));
            border-radius: 2rem;
            padding: 2rem;
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .dashboard-eyebrow {
            letter-spacing: 0.4em;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .dashboard-subtitle {
            opacity: 0.85;
            max-width: 32rem;
        }

        .dashboard-filter {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .dashboard-filter__label {
            font-size: 0.75rem;
            letter-spacing: 0.3em;
        }

        .dashboard-filter__select {
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.95);
            padding: 0.6rem 0.85rem;
            color: #0f172a;
            min-width: 220px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            background-repeat: no-repeat;
            padding-right: 1.5rem;
            font-weight: 600;
        }

        .dashboard-filter__select option {
            color: #0f172a;
            background-color: #fff;
        }

        .dark .dashboard-filter__select {
            border-color: rgba(226,232,240,0.4);
            background: rgba(15,23,42,0.85);
            color: #e2e8f0;
        }

        .dark .dashboard-filter__select option {
            color: #0f172a;
        }

        .mutun-dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
        }

        .dashboard-card {
            border-radius: 1.5rem;
            padding: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            box-shadow: 0 20px 45px rgba(15,23,42,0.15);
        }

        .dashboard-card--success {
            background: linear-gradient(135deg, #34d399, #059669);
        }

        .dashboard-card--mutqin {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }

        .dashboard-card__label {
            font-size: 0.75rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .dashboard-card__value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .dashboard-card__inline {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .dashboard-card__badge {
            border-radius: 999px;
            padding: 0.25rem 0.85rem;
            border: 1px solid rgba(255,255,255,0.4);
            font-weight: 600;
        }

        .dashboard-percentages {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .percentage-card {
            border-radius: 1.25rem;
            padding: 1.25rem;
            background: var(--md-card-bg);
            border: 1px solid var(--md-border);
            box-shadow: 0 15px 35px rgba(15,23,42,0.07);
            color: var(--md-text);
        }

        .percentage-card__title {
            text-transform: uppercase;
            letter-spacing: 0.3em;
            font-size: 0.75rem;
            color: var(--md-muted);
        }

        .percentage-card__value {
            display: flex;
            align-items: baseline;
            gap: 0.4rem;
            margin-top: 0.5rem;
        }

        .percentage-card__number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .percentage-card__unit {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--md-muted);
        }

        .percentage-card__label {
            margin-top: 0.25rem;
            font-size: 0.9rem;
            color: var(--md-muted);
        }

        .dashboard-widget {
            border-radius: 1.75rem;
            border: 1px solid var(--md-border);
            background: var(--md-card-bg);
            padding: 1.5rem;
            box-shadow: 0 15px 35px rgba(15,23,42,0.08);
            color: var(--md-text);
        }

        .dashboard-widget__header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .dashboard-widget__header p {
            font-size: 0.9rem;
            color: var(--md-muted);
        }

        .chart-card {
            border-radius: 1.25rem;
            border: 1px solid var(--md-border);
            padding: 1rem;
            background: var(--md-soft-bg);
        }

        .chart-card__canvas {
            height: 220px;
        }

        .chart-card__canvas canvas {
            width: 100%;
            height: 100%;
        }

        .chart-card__header h3 {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--md-text);
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--md-text);
        }

        .dashboard-table thead th {
            text-align: left;
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--md-muted);
            border-bottom: 1px solid var(--md-border);
            padding: 0.75rem;
        }

        .dashboard-table tbody td {
            padding: 0.85rem;
            border-bottom: 1px solid var(--md-border);
            font-size: 0.95rem;
        }
    </style>
@endpush

@push('scripts')
    @if (!empty($lineCharts))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const charts = @json($lineCharts);

                charts.forEach((chart) => {
                    const ctx = document.getElementById(chart.canvas_id);
                    if (!ctx) {
                        return;
                    }

                    new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: chart.labels,
                            datasets: [{
                                label: `Setoran ${chart.kitab}`,
                                data: chart.data,
                                borderColor: '#7c3aed',
                                backgroundColor: 'rgba(124,58,237,0.15)',
                                tension: 0.35,
                                fill: true,
                                pointRadius: 3,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                }
                            }
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
