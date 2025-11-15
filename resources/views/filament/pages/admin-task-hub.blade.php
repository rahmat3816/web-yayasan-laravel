<x-filament::page>
    <div class="task-hub space-y-10">
        {{-- Hero --}}
<section class="task-hub__hero text-white">
            <div class="task-hub__hero-grid">
                <div class="space-y-4">
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight font-display">
                        Selamat datang kembali, {{ $user->name }}
                    </h1>
                    <p class="text-sm md:text-base text-white/80 max-w-xl">
                        Pantau progres unit pendidikan, kelola tugas lintas jabatan, dan kolaborasikan laporan real-time langsung dari satu command center.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <x-filament::button tag="a" size="sm" class="rounded-full shadow-lg shadow-primary-500/30" color="primary" href="{{ url('/filament') }}">
                            Buka Control Panel
                        </x-filament::button>
                        <x-filament::button tag="a" size="sm" class="rounded-full bg-white/90 text-slate-900 border border-white/80 hover:bg-white/95 dark:bg-transparent dark:border-white/60 dark:text-white dark:hover:bg-white/10" color="gray" href="{{ route('guru.dashboard') }}">
                            Lihat Dashboard Guru
                        </x-filament::button>
                    </div>
                </div>

                <div class="task-hub__snapshot glass-card">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-500 dark:text-white/70">Snapshot</p>
                    <p class="text-4xl font-semibold mt-2 text-slate-900 dark:text-white">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-white/70">Unit Aktif</p>
                            <p class="text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['totalUnits'] ?? ($panelSections ? count($panelSections) : 0)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-white/70">Tugas Prioritas</p>
                            <p class="text-3xl font-semibold text-slate-900 dark:text-white">{{ count($cards) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        @if (!empty($stats))
            <section>
                <div class="task-hub__stats">
                    <livewire:dashboard-stats :metrics="$stats" />
                </div>
            </section>
        @endif

        {{-- Action cards --}}
        @include('dashboard.partials.action-cards', ['cards' => $cards])

        {{-- Chart placeholder --}}
        <section class="task-hub__placeholder glass-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Insights</p>
                    <h2 class="text-xl font-semibold text-slate-800 dark:text-white">Tren Laporan & Hafalan</h2>
                </div>
                <x-filament::button tag="button" size="sm" class="rounded-full" color="gray">
                    Lihat Detail
                </x-filament::button>
            </div>
            <div class="task-hub__placeholder-body">
                <div class="text-center text-slate-400 dark:text-slate-500 space-y-1">
                    <p class="text-lg font-semibold">Chart interaktif segera hadir</p>
                    <p class="text-sm">Integrasi ke dataset Livewire + ApexCharts tersedia pada sprint berikutnya.</p>
                </div>
            </div>
        </section>

        {{-- Panel modules --}}
        @if (!empty($panelSections))
            <section class="space-y-6">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-gray-100">Modul Jabatan Anda</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($panelSections as $section)
                        <div class="glass-card p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ $section['label'] }}</p>
                                <span class="task-hub__chip">{{ count($section['items']) }} modul</span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($section['items'] as $panel)
                                    <a href="{{ $panel['url'] }}"
                                       class="block rounded-2xl px-4 py-3 border border-white/40 dark:border-slate-700 hover:bg-white/60 dark:hover:bg-slate-800/60 transition">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $panel['title'] }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $panel['description'] }}</p>
                                        <span class="text-xs text-primary mt-2 inline-flex items-center gap-1">Buka modul -></span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

@push('styles')
    <style>
        .task-hub {
            --hub-glass-border: rgba(255, 255, 255, 0.4);
            --hub-glass-bg: rgba(255, 255, 255, 0.85);
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }

            .task-hub__hero {
                border-radius: 2rem;
                padding: 2.5rem;
                background: radial-gradient(circle at top left, rgba(79, 70, 229, 0.95), rgba(14, 165, 233, 0.85));
                position: relative;
                overflow: hidden;
                color: #fff;
            }

            .task-hub__hero::after {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.18), transparent 45%);
                pointer-events: none;
            }

            .task-hub__hero-grid {
                position: relative;
                z-index: 10;
                display: grid;
                gap: 2rem;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            }

            .task-hub__badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                border-radius: 999px;
                padding: 0.35rem 1rem;
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                background-color: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.35);
            }

            .task-hub .glass-card {
                background: var(--hub-glass-bg);
                border: 1px solid var(--hub-glass-border);
                border-radius: 1.5rem;
                box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
                padding: 1.5rem;
            }

            .task-hub__snapshot {
                background: rgba(248,250,252,0.95);
                border-color: rgba(148,163,184,0.2);
                color: #0f172a;
            }

            html.dark .task-hub {
                --hub-glass-border: rgba(15, 23, 42, 0.65);
                --hub-glass-bg: rgba(15, 23, 42, 0.85);
            }

            html.dark .task-hub .glass-card {
                box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
            }

            html.dark .task-hub__hero {
                background: radial-gradient(circle at top left, rgba(99,102,241,0.8), rgba(14,165,233,0.6));
            }

            html.dark .task-hub__snapshot {
                background: rgba(15,23,42,0.55);
                border-color: rgba(255,255,255,0.25);
                color: #fff;
            }

            html.dark .task-hub__chip {
                border-color: rgba(148,163,184,0.35);
            }

            .task-hub__placeholder {
                border-radius: 1.75rem;
                background: rgba(255,255,255,0.98);
                border: 1px solid rgba(148,163,184,0.2);
                color: #0f172a;
            }

            .task-hub__placeholder-body {
                margin-top: 1.5rem;
                border-radius: 1.5rem;
                border: 1px dashed rgba(148,163,184,0.4);
                background: rgba(248,250,252,0.85);
                min-height: 10rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #475569;
            }

            html.dark .task-hub__placeholder {
                background: rgba(15,23,42,0.8);
                border-color: rgba(71, 85, 105, 0.45);
            }

            html.dark .task-hub__placeholder-body {
                background: rgba(15,23,42,0.6);
                border-color: rgba(71,85,105,0.45);
            }

            .task-hub__chip {
                padding: 0.2rem 0.9rem;
                border-radius: 999px;
                border: 1px solid rgba(148,163,184,0.4);
                font-size: 0.75rem;
            }

            .task-hub__stats {
                margin-top: 1rem;
            }

            .task-hub__placeholder {
                margin-top: 1rem;
            }

            @media (max-width: 768px) {
                .task-hub__hero {
                    padding: 1.75rem;
                }
            }
        </style>
    @endpush
</x-filament::page>
