<div class="glass-card p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Riwayat Hafalan Anak</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Grafik kumulatif ayat yang disetorkan.</p>
        </div>

        @if ($santriList->count())
            <select wire:model.live="selectedSantriId"
                    data-wali-select
                    class="select select-bordered select-sm bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-100">
                @foreach ($santriList as $santri)
                    <option value="{{ $santri->id }}">{{ $santri->nama }}</option>
                @endforeach
            </select>
        @endif
    </div>

    @if ($santriList->isEmpty())
        <p class="text-center text-slate-400 py-8 text-sm">Belum ada data santri yang terhubung.</p>
    @elseif (empty($timeline['labels']))
        <p class="text-center text-slate-400 py-8 text-sm">Santri ini belum memiliki setoran hafalan.</p>
    @else
        <div wire:ignore>
            <canvas id="waliTimelineChart" height="200"></canvas>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endonce

@push('scripts')
    <style>
        select[data-wali-select] option {
            background-color: #fff;
            color: #0f172a;
        }
        html[data-theme='emerald-dark'] select[data-wali-select] option {
            background-color: #0f172a;
            color: #e2e8f0;
        }
    </style>
    <script>
        window.waliTimelineManager = window.waliTimelineManager || {
            chart: null,
            render(payload) {
                const canvas = document.getElementById('waliTimelineChart');
                if (!canvas) return;
                if (this.chart) this.chart.destroy();
                this.chart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: payload,
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Akumulasi Ayat Disetorkan' },
                        },
                        scales: {
                            y: { beginAtZero: true },
                        },
                    },
                });
            }
        };
        window.waliTimelineManager.render({!! json_encode($timeline) !!});
    </script>
@endpush
