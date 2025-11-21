<x-filament::page class="rekap-page">
    <div class="setoran-wrapper space-y-8">
        <section class="setoran-hero rekap-hero">
            <div class="setoran-hero__content">
                <p class="setoran-eyebrow">Keamanan</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                    Rekap Pelanggaran Santri
                </h1>
                <p class="text-sm md:text-base text-white/85 max-w-2xl">
                    Pantau akumulasi poin pelanggaran, status SP, dan riwayat terbaru dengan filter unit, kategori, dan tahun.
                </p>
            </div>
            <form method="GET" action="{{ route('filament.admin.pages.keamanan.rekap-pelanggaran') }}" class="rekap-filters">
                @php
                    $yearOptions = range(now()->year + 1, now()->year - 5);
                @endphp
                <div class="filter-grid filter-grid--wide">
                    <div class="filter-control">
                        <label class="field-label">Unit</label>
                        <select name="unit_id" class="setoran-select select-plain">
                            <option value="">Semua Unit</option>
                            @foreach($unitOptions as $unit)
                                <option value="{{ $unit['id'] }}" @selected($filters['unit_id'] == $unit['id'])>
                                    {{ $unit['nama'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-control">
                        <label class="field-label">Kategori</label>
                        <select name="kategori_id" class="setoran-select select-plain">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoriOptions as $kat)
                                <option value="{{ $kat->id }}" @selected($filters['kategori_id'] == $kat->id)>
                                    {{ $kat->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-control">
                        <label class="field-label">Tahun</label>
                        <select name="tahun" class="setoran-select select-plain">
                            @foreach($yearOptions as $y)
                                <option value="{{ $y }}" @selected((int)$filters['tahun'] === (int)$y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="setoran-primary-btn">Terapkan</button>
                    <a href="{{ route('filament.admin.pages.keamanan.rekap-pelanggaran') }}" class="setoran-secondary-btn">Reset</a>
                </div>
            </form>
        </section>

        <section class="stat-grid">
            <div class="stat-card">
                <p class="stat-label">Total Pelanggaran</p>
                <p class="stat-value">{{ number_format($stats['total_logs']) }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Santri Terlibat</p>
                <p class="stat-value">{{ number_format($stats['distinct_santri']) }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Poin</p>
                <p class="stat-value">{{ number_format($stats['total_poin']) }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">SP1 / SP2 / SP3</p>
                <p class="stat-value">{{ $stats['sp1'] }} / {{ $stats['sp2'] }} / {{ $stats['sp3'] }}</p>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h3 class="card-title">Tren Pelanggaran</h3>
            </div>
            @php
                $chartLabels = $trendMonthly->pluck('ym');
                $chartCounts = $trendMonthly->pluck('total');
                $chartPoints = $trendMonthly->pluck('total_poin');
            @endphp
            @if($chartLabels->count())
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <p class="text-sm text-white/70 p-4">Belum ada data untuk ditampilkan.</p>
            @endif
        </section>

        <section class="card">
            <div class="card-header">
                <h3 class="card-title">Pelanggaran per Kategori</h3>
            </div>
            <div class="divide-y divide-white/5">
                @forelse($categoryBreakdown as $row)
                    <div class="breakdown-row">
                        <div>
                            <p class="font-semibold text-white">{{ $row['nama'] }}</p>
                            <p class="text-sm text-white/60">Total pelanggaran</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-white">{{ $row['total'] }}</p>
                            <p class="text-sm text-white/60">{{ $row['poin'] }} poin</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-white/70 p-4">Belum ada data pelanggaran.</p>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-white/90">
                    <thead>
                        <tr class="text-left border-b border-white/10">
                            <th class="py-2 pr-3">Santri</th>
                            <th class="py-2 pr-3">Unit</th>
                            <th class="py-2 pr-3">Pelanggaran</th>
                            <th class="py-2 pr-3">Kategori</th>
                            <th class="py-2 pr-3">Poin</th>
                            <th class="py-2 pr-3">SP</th>
                            <th class="py-2 pr-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                            <tr class="border-b border-white/5">
                                <td class="py-2 pr-3">{{ $log->santri->nama ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $log->santri->unit->nama_unit ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $log->type->nama ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $log->kategori->nama ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $log->poin }}</td>
                                <td class="py-2 pr-3">SP{{ $log->sp_level }}</td>
                                <td class="py-2 pr-3">{{ $log->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-white/70">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            .rekap-hero {
                background: radial-gradient(120% 120% at 0% 0%, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.15)),
                            linear-gradient(120deg, #0b1225 0%, #112449 60%, #0b1225 100%);
            }
            .rekap-filters {
                margin-top: 1.5rem;
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 1rem;
                padding: 1rem;
                backdrop-filter: blur(6px);
            }
            .filter-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
                row-gap: 1.1rem;
            }
            .filter-grid--wide {
                grid-template-columns: 1.2fr 1.2fr 0.8fr;
            }
            .filter-actions {
                margin-top: 0.75rem;
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
            }
            .stat-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
            .stat-card {
                background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 1rem;
                padding: 1rem 1.25rem;
            }
            .stat-label { color: rgba(255,255,255,0.75); font-size: 0.85rem; }
            .stat-value { color: #fff; font-size: 1.6rem; font-weight: 700; }
            .card {
                background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
                border: 1px solid rgba(255,255,255,0.06);
                border-radius: 1.2rem;
                padding: 1rem 1.25rem;
            }
            .card-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.5rem; }
            .card-title { color: #fff; font-weight: 700; }
            .breakdown-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.9rem 0;
            }
            .setoran-select, .setoran-input {
                background: #0f172a;
                border: 1px solid rgba(255,255,255,0.08);
                color: #fff;
            }
            .setoran-select.select-plain {
                background-image: none;
            }
            .select-plain {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                background-image: none;
                padding-right: 1rem;
            }
            .chart-container { position: relative; width: 100%; height: 320px; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('trendChart');
                if (!ctx) return;
                const labels = @json($chartLabels);
                const counts = @json($chartCounts);
                const points = @json($chartPoints);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total Pelanggaran',
                                data: counts,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34,197,94,0.12)',
                                tension: 0.25,
                                fill: true,
                                borderWidth: 2,
                            },
                            {
                                label: 'Total Poin',
                                data: points,
                                borderColor: '#60a5fa',
                                backgroundColor: 'rgba(96,165,250,0.12)',
                                tension: 0.25,
                                fill: true,
                                borderWidth: 2,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#e5e7eb' } },
                        },
                        scales: {
                            x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        },
                    }
                });
            });
        </script>
    @endpush
    @include('filament.pages.partials.setoran-styles')
</x-filament::page>
