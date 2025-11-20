@php
    use Illuminate\Support\Str;
    $santriList = $santriList ?? collect();
    $selectedSantriId = $selectedSantriId ?? null;
    $selectedSantriFilter = $selectedSantriFilter ?? ($selectedSantriId ? (string) $selectedSantriId : 'all');
    $selectedSantri = $santriList->firstWhere('id', $selectedSantriId);
    $rekap = $rekap ?? [];
    $recentSetorans = $recentSetorans ?? collect();
    $selectedTargets = $selectedTargets ?? collect();
    $selectedTargetSummaries = $selectedTargetSummaries ?? collect();
    $cardSetoranMap = $cardSetoranMap ?? collect();
@endphp

<x-filament::page class="setoran-page">
@if(!empty($forbidden))
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 text-center space-y-3">
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Maaf, halaman ini khusus untuk Pengampu Tahfizh Mutun.</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">Silakan hubungi admin jika Anda merasa memiliki akses.</p>
    </div>
    @php return; @endphp
@endif

@if ($santriList->isEmpty())
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 text-center space-y-3">
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Belum ada santri yang dapat Anda kelola.</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">Pastikan Anda sudah ditetapkan sebagai pengampu sebuah halaqoh.</p>
    </div>
    @php return; @endphp
@endif

<div class="setoran-wrapper">
    <section class="setoran-hero">
        <div class="setoran-hero__content">
            <div class="space-y-3">
                <p class="setoran-eyebrow">Tahfizh Mutun</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight">Setoran Hafalan Mutun</h1>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="setoran-pill">
                        Santri Aktif: {{ $santriList->count() }}
                    </span>
                    <span class="setoran-pill">
                        Target Aktif: {{ $rekap['target_aktif'] ?? 0 }}
                    </span>
                    <span class="setoran-pill">
                        Total Setoran: {{ $rekap['total_setoran'] ?? 0 }}
                    </span>
                </div>
            </div>
            <div class="setoran-hero__form">
                <form method="GET" action="{{ route('filament.admin.pages.tahfizh.mutun.setoran') }}" class="space-y-3">
                    <label class="text-xs font-semibold text-white tracking-wide uppercase">Pilih Santri</label>
                    <select name="santri_id" class="setoran-select w-full" onchange="this.form.submit()">
                        <option value="all" @selected($selectedSantriFilter === 'all')>Semua Santri</option>
                        @foreach ($santriList as $s)
                            <option value="{{ $s->id }}" @selected((string) $selectedSantriFilter === (string) $s->id)>
                                {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </section>

    <section class="setoran-stats grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-2">
        <article class="setoran-stat-card">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Total Setoran</p>
                <p class="setoran-stat-value">{{ $rekap['total_setoran'] ?? 0 }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8 text-white/80" />
        </article>
        <article class="setoran-stat-card setoran-stat-card--accent">
            <div class="setoran-stat-card__content">
                <p class="setoran-stat-label">Nilai Mutqin Rata-rata</p>
                <p class="setoran-stat-value">{{ number_format($rekap['avg_mutqin'] ?? 0, 1) }}</p>
            </div>
            <x-filament::icon icon="heroicon-o-star" class="h-8 w-8 text-white/80" />
        </article>
    </section>

    <section class="setoran-card text-slate-900 dark:text-white">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Santri Tahfizh Mutun</h2>
                <p class="text-sm text-slate-500 dark:text-slate-300">Kelola setoran per santri berdasarkan target mutun.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($santriList as $s)
                <article class="santri-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">Santri</p>
                            <h3 class="text-lg font-semibold">{{ $s->nama }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $s->unit->nama_unit ?? 'Unit '.$s->unit_id }}
                            </p>
                        </div>
                        <span class="santri-chip {{ $s->jenis_kelamin === 'P' ? 'chip-danger' : 'chip-warning' }}">
                            {{ $s->jenis_kelamin === 'P' ? 'Putri' : 'Putra' }}
                        </span>
                    </div>
                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-300">Halaqoh</dt>
                            <dd class="font-semibold">{{ optional($s->halaqoh->first())->nama_halaqoh ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-300">Target Mutun</dt>
                            <dd class="font-semibold">{{ $s->mutun_targets_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-300">Setoran</dt>
                            <dd class="font-semibold">
                                {{ $cardSetoranMap[$s->id]['count'] ?? 0 }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-300">Setoran Terakhir</dt>
                            <dd class="font-semibold text-sm">
                                {{ $cardSetoranMap[$s->id]['last_date'] ?? 'Belum ada' }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-6">
                        <a href="{{ route('filament.admin.pages.mutun-setorans.create', ['santri_id' => $s->id]) }}"
                           class="setoran-button setoran-button--action w-full justify-center">
                            Kelola Setoran
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="setoran-card text-slate-900 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Setoran Terbaru</h2>
                <p class="text-sm text-slate-500 dark:text-slate-300">8 catatan setoran terakhir pada santri yang Anda kelola.</p>
            </div>
        </div>
        <div class="mt-6 overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Santri</th>
                        <th>Kitab</th>
                        <th>Mutun</th>
                        <th>Penilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSetorans as $setoran)
                        <tr>
                            <td>{{ optional($setoran->tanggal)->format('d M Y') }}</td>
                            <td>{{ $setoran->target->santri->nama ?? '-' }}</td>
                            <td>{{ $setoran->target->mutun->kitab ?? '-' }}</td>
                            <td>
                                <span class="font-semibold">Mutun {{ $setoran->target->mutun->nomor ?? $setoran->target->mutun->urutan ?? '-' }}</span>
                                <p class="text-xs text-slate-500 dark:text-slate-300">{{ $setoran->target->mutun->judul ?? '-' }}</p>
                            </td>
                            <td>{{ $setoran->penilai->nama ?? '-' }}</td>
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

    @if ($selectedSantri)
        <section class="setoran-card text-slate-900 dark:text-white">
            <div class="space-y-2">
                <h2 class="text-lg font-semibold">Target Mutun � {{ $selectedSantri->nama }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-300">Ringkasan target per tahun dan kitab.</p>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Semester</th>
                            <th>Kitab</th>
                            <th>Mutun Awal</th>
                            <th>Mutun Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($selectedTargetSummaries as $summary)
                            <tr>
                                <td>{{ $summary['tahun'] }}</td>
                                <td>{{ Str::headline($summary['semester'] ?? '-') }}</td>
                                <td>{{ $summary['kitab'] }}</td>
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        Mutun {{ $summary['mutun_awal']['nomor'] ?? '-' }}
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-300">
                                        {{ $summary['mutun_awal']['judul'] ?? '-' }}
                                    </p>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        Mutun {{ $summary['mutun_akhir']['nomor'] ?? '-' }}
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-300">
                                        {{ $summary['mutun_akhir']['judul'] ?? '-' }}
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-slate-500">Belum ada target mutun untuk santri ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>

@include('filament.pages.partials.setoran-styles')
</x-filament::page>
