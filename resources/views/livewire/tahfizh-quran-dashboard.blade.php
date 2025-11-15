@php
    $cards = [
        ['label' => 'Total Setoran', 'value' => number_format($stats['totalSetoran'] ?? 0), 'icon' => '', 'gradient' => 'from-indigo-500/90 via-violet-500/80 to-purple-500/70'],
        ['label' => 'Setoran Hari Ini', 'value' => number_format($stats['setoranHariIni'] ?? 0), 'icon' => '', 'gradient' => 'from-amber-500/90 via-orange-400/80 to-yellow-400/70'],
        ['label' => 'Santri Aktif', 'value' => number_format($stats['santriAktif'] ?? 0), 'icon' => '', 'gradient' => 'from-sky-500/90 via-cyan-400/80 to-teal-400/70'],
        ['label' => 'Guru Pengampu', 'value' => number_format($stats['guruAktif'] ?? 0), 'icon' => '', 'gradient' => 'from-emerald-500/90 via-green-400/80 to-lime-400/70'],
    ];
@endphp

<div class="space-y-6" wire:poll.60s>
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Program Unggulan</p>
            <h2 class="text-xl font-semibold text-slate-800 dark:text-white">Dashboard Tahfizh Qur'an</h2>
        </div>
        <div class="flex flex-wrap gap-3">
            <div class="form-control w-32">
                <label class="label text-xs text-slate-500">Tahun</label>
                <select wire:model.live="year" class="select select-bordered select-sm">
                    @foreach ($yearOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-control w-36">
                <label class="label text-xs text-slate-500">Bulan</label>
                <select wire:model.live="month" class="select select-bordered select-sm">
                    @foreach ($monthOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($unitName)
                <div class="form-control w-44">
                    <label class="label text-xs text-slate-500">Unit</label>
                    <span class="badge badge-outline">{{ $unitName }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <article class="glass-card p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl text-white bg-gradient-to-br {{ $card['gradient'] }} shadow-lg">
                    {{ $card['icon'] }}
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ $card['label'] }}</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $card['value'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="chart-placeholder">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Tren Bulan Ini</p>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Setoran per Hari</h3>
                </div>
                <span class="badge badge-sm badge-outline">{{ $trend->count() }} data</span>
            </div>
            @if ($trend->isEmpty())
                <div class="flex h-48 items-center justify-center text-slate-400">
                    Belum ada setoran pada bulan ini.
                </div>
            @else
                <div class="h-48 flex items-end gap-3">
                    @foreach ($trend as $point)
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full rounded-t-2xl bg-gradient-to-t from-indigo-500 to-sky-400 shadow-lg"
                                 style="height: {{ max(10, $point['value'] * 8) }}px"></div>
                            <p class="text-[10px] mt-1 text-slate-500">{{ $point['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="glass-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Leaderboard</p>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Top Santri</h3>
                </div>
                <span class="badge badge-sm badge-outline">{{ $topSantri->count() }} santri</span>
            </div>
            <div class="space-y-3">
                @forelse ($topSantri as $rank => $row)
                    <div class="glass-border rounded-2xl px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-semibold">{{ $rank + 1 }}</span>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-white">{{ $row['nama'] }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($row['total']) }} setoran</p>
                            </div>
                        </div>
                        <span class="text-xs text-slate-400">ID {{ $row['santri_id'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada data leaderboard.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="glass-card p-5 grid gap-4 md:grid-cols-2">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Rekap Halaman</p>
            <h3 class="text-2xl font-semibold">{{ $rekap['total_halaman'] ?? 0 }} halaman</h3>
            <p class="text-sm text-slate-500">~ {{ $rekap['total_juz'] ?? 0 }} juz</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Progress Surah</p>
                <p class="text-xl font-semibold">{{ $rekap['progress_surah'] ?? 0 }}%</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Progress Juz</p>
                <p class="text-xl font-semibold">{{ $rekap['progress_juz'] ?? 0 }}%</p>
            </div>
        </div>
    </div>
</div>
