<div class="space-y-6">
{{-- ==============================
Tahfizh Dashboard (Versi Terbaru)
Tujuan: Menampilkan data real hafalan per halaqoh menggunakan Chart.js
File: resources/views/tahfizh/dashboard.blade.php
============================== --}}

<x-breadcrumb />

@php
    $cards = [
        [
            'title' => 'Kelola Halaqoh',
            'description' => 'Atur guru pengampu dan santri.',
            'url' => route('tahfizh.halaqoh.index'),
            'icon' => '',
        ],
        [
            'title' => 'Tambah Halaqoh',
            'description' => 'Buat halaqoh baru dan tetapkan pengampu.',
            'url' => route('tahfizh.halaqoh.create'),
            'icon' => '',
        ],
        [
            'title' => 'Rekap Hafalan',
            'description' => 'Unduh rekap progres per halaqoh.',
            'url' => route('tahfizh.dashboard'),
            'icon' => '',
        ],
    ];
@endphp

@php
    $monthlyConfig = $percentageSeries['monthly'] ?? [];
    $semesterConfig = $percentageSeries['semester'] ?? [];
    $monthlyOptions = $monthlyConfig['options'] ?? [];
    $semesterOptions = $semesterConfig['options'] ?? [];
    $monthlyCurrentLabel = collect($monthlyOptions)->firstWhere('key', $monthlyConfig['current_key'] ?? null)['label'] ?? 'Bulan Berjalan';
    $semesterCurrentLabel = collect($semesterOptions)->firstWhere('key', $semesterConfig['current_key'] ?? null)['label'] ?? 'Semester Berjalan';
@endphp

@include('dashboard.partials.action-cards', ['cards' => $cards])

{{-- Statistik Utama --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="p-4 bg-indigo-100 dark:bg-indigo-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Halaqoh</h3>
        <p class="text-3xl font-bold">{{ $totalHalaqoh }}</p>
    </div>
    <div class="p-4 bg-teal-100 dark:bg-teal-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Santri</h3>
        <p class="text-3xl font-bold">{{ $totalSantri }}</p>
    </div>
    <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Setoran</h3>
        <p class="text-3xl font-bold">{{ $totalHafalan }}</p>
    </div>
</div>

{{-- Grafik Hafalan --}}
<div class="mt-10 grid gap-6 lg:grid-cols-2">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Hafalan per Halaqoh</h2>
        <canvas id="halaqohChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">Hafalan per Santri (Top 10)</h2>
        <canvas id="santriChart" height="200"></canvas>
    </div>
</div>

<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    @php $hasTimeline = !empty($santriTimeline['datasets']); @endphp
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Riwayat Setoran per Santri</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Data kumulatif ayat yang disetorkan.</p>
        </div>
        @if($santriCandidates->count())
            <form id="timeline-picker" data-endpoint="{{ route('tahfizh.dashboard.timeline') }}" class="flex items-center gap-2">
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-sm btn-outline flex items-center gap-2 rounded-full" id="timeline-picker-label">
                        <span>{{ $selectedSantriName }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content menu bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-64 mt-2 max-h-60 overflow-y-auto">
                        @foreach ($santriCandidates as $candidate)
                            <li>
                                <button type="button" class="flex flex-col items-start js-santri-option"
                                        data-santri-option="{{ $candidate->id }}"
                                        data-santri-name="{{ $candidate->nama }}">
                                    <span class="font-semibold text-slate-700 dark:text-slate-100">{{ $candidate->nama }}</span>
                                    <span class="text-xs text-slate-500">{{ number_format($candidate->total_ayat) }} ayat</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </form>
        @endif
    </div>
    @if (!$hasTimeline)
        <div class="text-center text-slate-400 py-8" id="timeline-empty-state">Belum ada data riwayat setoran.</div>
    @else
        <div id="timeline-empty-state" class="hidden"></div>
    @endif
    <canvas id="santriTimelineChart" height="140" class="{{ $hasTimeline ? '' : 'hidden' }}"></canvas>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Capaian Target Hafalan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbandingan target tahunan vs realisasi setoran.</p>
        </div>
    </div>
    <canvas id="targetProgressChart" height="200"></canvas>
</div>

<div class="mt-10 grid gap-6 lg:grid-cols-3">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Bulanan</h2>
                <p class="text-xs text-slate-500">Fokus bulan berjalan, bisa lihat bulan sebelumnya dari dropdown.</p>
            </div>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-sm btn-outline rounded-full flex items-center gap-2" id="monthlyPeriodLabel">
                    <span class="period-label">{{ $monthlyCurrentLabel }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-56 mt-2 max-h-60 overflow-y-auto" id="monthlyPeriodSelect">
                    @forelse ($monthlyOptions as $option)
                        <li><button type="button" data-key="{{ $option['key'] }}" data-label="{{ $option['label'] }}">{{ $option['label'] }}</button></li>
                    @empty
                        <li><span class="px-4 py-2 text-xs text-slate-400">Belum ada data</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
        <canvas id="monthlyPercentageChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Semester</h2>
                <p class="text-xs text-slate-500">Bandingkan semester berjalan dengan sebelumnya.</p>
            </div>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-sm btn-outline rounded-full flex items-center gap-2" id="semesterPeriodLabel">
                    <span class="period-label">{{ $semesterCurrentLabel }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-48 mt-2" id="semesterPeriodSelect">
                    @forelse ($semesterOptions as $option)
                        <li><button type="button" data-key="{{ $option['key'] }}" data-label="{{ $option['label'] }}">{{ $option['label'] }}</button></li>
                    @empty
                        <li><span class="px-4 py-2 text-xs text-slate-400">Belum ada data</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
        <canvas id="semesterPercentageChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-2 gap-2">
            <div>
                <h2 class="text-xl font-semibold mb-1">Persentase Tahunan</h2>
                <p class="text-xs text-slate-500">Pantau tahun berjalan dan bandingkan dengan tahun sebelumnya.</p>
            </div>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-sm btn-outline rounded-full flex items-center gap-2" id="annualPeriodLabel">
                    <span class="period-label">Tahun Berjalan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-48 mt-2" id="annualPeriodSelect">
                    <li><button type="button" data-period="current">Tahun Berjalan</button></li>
                    <li><button type="button" data-period="previous">Tahun Sebelumnya</button></li>
                </ul>
            </div>
        </div>
        <canvas id="annualPercentageChart" height="200"></canvas>
    </div>
</div>

@if(!empty($actualCoverageSummary))
<div class="mt-10 bg-white dark:bg-gray-900 rounded-3xl border border-emerald-50/80 shadow-xl p-6 md:p-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Capaian Hafalan Aktual</h3>
            <p class="text-sm text-slate-500 dark:text-slate-300">Rekap total juz, halaman, surah, dan ayat dari setoran.</p>
        </div>
    </div>
    <div class="overflow-x-auto" data-coverage-endpoint="{{ route('tahfizh.coverage.detail', '__SANTRI__') }}">
        <table class="table">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-widest">
                    <th>Santri</th>
                    <th>Total Juz</th>
                    <th>Total Halaman</th>
                    <th>Total Surah</th>
                    <th>Total Ayat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($actualCoverageSummary as $summary)
                    <tr class="hover:bg-emerald-50/40">
                        <td class="font-semibold text-slate-800">{{ $summary['santri'] }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_juz'], 1) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_halaman']) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_surah']) }}</td>
                        <td class="text-center font-semibold text-slate-700">{{ number_format($summary['total_ayat']) }}</td>
                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline" data-coverage-detail="{{ $summary['santri_id'] }}" data-santri-name="{{ $summary['santri'] }}">Detail</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<dialog id="coverageDetailModal" class="modal">
    <div class="modal-box max-w-3xl bg-white dark:bg-slate-900">
        <h3 class="font-bold text-lg text-slate-800 dark:text-white" id="coverageDetailTitle">Detail Hafalan</h3>
        <div class="py-4 max-h-96 overflow-y-auto" id="coverageDetailBody">
            <p class="text-sm text-slate-500">Memuat data...</p>
        </div>
        <div class="modal-action">
            <form method="dialog"><button class="btn">Tutup</button></form>
        </div>
    </div>
</dialog>

@php
    $targetOld = [
        'santri_id' => old('santri_id'),
        'tahun' => old('tahun'),
        'juz' => old('juz'),
        'surah_start_id' => old('surah_start_id'),
        'surah_end_id' => old('surah_end_id'),
        'ayat_start' => old('ayat_start'),
        'ayat_end' => old('ayat_end'),
    ];
    $suratEndpoint = route('tahfizh.ajax.suratByJuz', ['juz' => '__JUZ__']);
@endphp

<div class="mt-10">
    <div class="rounded-3xl border border-emerald-100 dark:border-emerald-500/20 bg-gradient-to-br from-white via-emerald-50 to-white dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 shadow-xl p-6 md:p-8 space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-emerald-500 font-semibold">Perencanaan Tahfizh</p>
                <h2 class="text-3xl font-semibold text-slate-900 dark:text-white mt-1">Target Hafalan Tahunan</h2>
                <p class="text-sm text-slate-500 dark:text-slate-300 mt-2">Tetapkan rentang juz-surah-ayat, lihat ringkasan otomatis, kemudian simpan.</p>
            </div>
            <div class="badge badge-outline border-emerald-300 text-emerald-600 px-4 py-3 rounded-full">Koordinator Tahfizh</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success rounded-2xl">
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('tahfizh.target.store') }}" class="space-y-6" id="targetPlanningForm">
            @csrf
            <div class="grid gap-4 md:grid-cols-3">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Santri</span></label>
                    <select name="santri_id" class="select select-bordered w-full bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" required>
                        <option value="">-- Pilih Santri --</option>
                        @foreach ($targetSantriOptions as $santri)
                            <option value="{{ $santri->id }}" @selected($targetOld['santri_id'] == $santri->id)>{{ $santri->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Tahun Target</span></label>
                    <select name="tahun" class="select select-bordered w-full bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" required>
                        <option value="">-- Pilih Tahun --</option>
                        @foreach ($targetYearOptions as $tahun)
                            <option value="{{ $tahun }}" @selected($targetOld['tahun'] == $tahun)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Juz</span></label>
                    <select name="juz" id="plannerJuz" class="select select-bordered w-full bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" required>
                        <option value="">-- Pilih Juz --</option>
                        @for ($i = 1; $i <= 30; $i++)
                            <option value="{{ $i }}" @selected($targetOld['juz'] == $i)>Juz {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Surah Awal</span></label>
                    <select name="surah_start_id" id="plannerSurahStart" class="select select-bordered w-full bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" data-old="{{ $targetOld['surah_start_id'] }}" required>
                        <option value="">-- Pilih Surah --</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Surah Akhir</span></label>
                    <select name="surah_end_id" id="plannerSurahEnd" class="select select-bordered w-full bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" data-old="{{ $targetOld['surah_end_id'] }}" required>
                        <option value="">-- Pilih Surah --</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Ayat Awal</span></label>
                    <input type="number" name="ayat_start" id="plannerAyatStart" class="input input-bordered bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" min="1" value="{{ $targetOld['ayat_start'] }}" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold text-slate-600">Ayat Akhir</span></label>
                    <input type="number" name="ayat_end" id="plannerAyatEnd" class="input input-bordered bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100" min="1" value="{{ $targetOld['ayat_end'] }}" required>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                <button type="button" class="btn btn-outline btn-sm" id="previewTargetRange">Pratinjau Ringkasan</button>
                <div class="text-sm text-slate-500" id="targetPreviewSummary">Belum ada ringkasan.</div>
            </div>

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <a href="{{ url()->current() }}" class="btn btn-ghost gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
                    Reset
                </a>
                <button type="submit" class="btn btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                    Simpan Target
                </button>
            </div>
        </form>
    </div>

    @if ($hafalanTargets->count())
        <div class="mt-6 rounded-3xl shadow-xl border border-slate-100/60 dark:border-slate-700/60 bg-gradient-to-br from-white via-slate-50 to-white dark:from-slate-900 dark:via-slate-900 dark:to-slate-950">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Daftar Target Aktif</h3>
                <p class="text-sm text-slate-500 dark:text-slate-300">Data ditampilkan berdasarkan entri terbaru.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr class="text-xs uppercase tracking-widest text-slate-500">
                            <th>Santri</th>
                            <th>Tahun</th>
                            <th>Juz</th>
                            <th>Surah Awal</th>
                            <th>Ayat Awal</th>
                            <th>Surah Akhir</th>
                            <th>Ayat Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hafalanTargets as $target)
                            <tr>
                                <td class="font-semibold">{{ optional($target->santri)->nama }}</td>
                                <td>{{ $target->tahun }}</td>
                                <td>{{ $target->juz }}</td>
                                <td>{{ optional($target->surahStart)->nama_surah ?? '-' }}</td>
                                <td>{{ $target->ayat_start }}</td>
                                <td>{{ optional($target->surahEnd)->nama_surah ?? '-' }}</td>
                                <td>{{ $target->ayat_end }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    const ctxT = document.getElementById('halaqohChart').getContext('2d');
    const halaqohChart = new Chart(ctxT, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hafalanPerHalaqoh->keys()) !!},
            datasets: [{
                label: 'Jumlah Hafalan per Halaqoh',
                data: {!! json_encode($hafalanPerHalaqoh->values()) !!},
                backgroundColor: 'rgba(147, 51, 234, 0.6)',
                borderColor: 'rgba(147, 51, 234, 1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' },
                title: {
                    display: true,
                    text: 'Rekap Hafalan Setiap Halaqoh',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const ctxS = document.getElementById('santriChart').getContext('2d');
    new Chart(ctxS, {
        type: 'pie',
        data: {
            labels: {!! json_encode($hafalanPerSantri->keys()) !!},
            datasets: [{
                data: {!! json_encode($hafalanPerSantri->values()) !!},
                backgroundColor: [
                    '#4f46e5','#22d3ee','#f97316','#10b981','#f43f5e',
                    '#a855f7','#fde047','#14b8a6','#fb7185','#60a5fa',
                ],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Distribusi Setoran per Santri',
                    font: { size: 16 }
                }
            }
        }
    });

    const timelineCanvas = document.getElementById('santriTimelineChart');
    const timelineEmptyState = document.getElementById('timeline-empty-state');
    const initialTimeline = {
        labels: {!! json_encode($santriTimeline['labels']) !!},
        datasets: {!! json_encode($santriTimeline['datasets']) !!}
    };

    if (timelineCanvas) {
        const datasets = initialTimeline.datasets && initialTimeline.datasets.length
            ? initialTimeline.datasets
            : [{
                label: 'Santri',
                data: [],
                borderColor: '#4f46e5',
                backgroundColor: '#4f46e5',
                tension: 0.3,
                fill: false,
            }];

        window.tahfizhTimelineChart = new Chart(timelineCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: initialTimeline.labels ?? [],
                datasets,
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Akumulasi Ayat Setoran per Santri' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        if (!initialTimeline.datasets || !initialTimeline.datasets.length) {
            timelineCanvas.classList.add('hidden');
            timelineEmptyState?.classList.remove('hidden');
        }
    }

    const timelinePicker = document.getElementById('timeline-picker');
    if (timelinePicker) {
        const endpoint = timelinePicker.dataset.endpoint;
        const labelEl = document.getElementById('timeline-picker-label');

        timelinePicker.querySelectorAll('[data-santri-option]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const santriId = btn.dataset.santriOption;
                const santriName = btn.dataset.santriName;

                try {
                    const response = await fetch(`${endpoint}?santri_id=${santriId}`);
                    if (!response.ok) throw new Error('Gagal memuat data');
                    const payload = await response.json();
                    updateTimelineChart(payload, santriName);
                    if (labelEl) {
                        labelEl.querySelector('span').textContent = santriName;
                    }
                } catch (error) {
                    console.error(error);
                }
            });
        });
    }

    function updateTimelineChart(payload, fallbackLabel = 'Santri') {
        if (!window.tahfizhTimelineChart) return;

        const labels = payload.labels ?? [];
        const dataset = payload.dataset ?? { label: fallbackLabel, data: [] };

        window.tahfizhTimelineChart.data.labels = labels;
        window.tahfizhTimelineChart.data.datasets = [{
            label: dataset.label || fallbackLabel,
            data: dataset.data || [],
            borderColor: '#4f46e5',
            backgroundColor: '#4f46e5',
            tension: 0.3,
            fill: false,
        }];
        window.tahfizhTimelineChart.update();

        if (dataset.data && dataset.data.length) {
            timelineCanvas?.classList.remove('hidden');
            timelineEmptyState?.classList.add('hidden');
        } else {
            timelineCanvas?.classList.add('hidden');
            if (timelineEmptyState) {
                timelineEmptyState.textContent = 'Santri ini belum memiliki setoran hafalan.';
                timelineEmptyState.classList.remove('hidden');
            }
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const progressData = @json($progressChart);
    const percentageSeries = @json($percentageSeries);
    const suratEndpointTpl = @json($suratEndpoint);
    const previewUrl = @json(route('tahfizh.target.preview'));
    const targetOld = @json($targetOld);

    const buildBarChart = (ctx, payload) => new Chart(ctx, {
        type: 'bar',
        data: {
            labels: (payload && payload.labels) || [],
            datasets: (payload && payload.datasets) || [{ label: 'Capaian (%)', data: [] }],
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } },
    });

    const applyBarSeries = (chart, payload) => {
        if (!chart) return;
        chart.data.labels = (payload && payload.labels) || [];
        chart.data.datasets = (payload && payload.datasets) || [{ label: 'Capaian (%)', data: [] }];
        chart.update();
    };

    const buildAnnualChart = (ctx, payload) => new Chart(ctx, {
        type: 'bar',
        data: {
            labels: (payload && payload.labels) || [],
            datasets: [{
                label: 'Capaian (%)',
                data: (payload && payload.values) || [],
                backgroundColor: 'rgba(244,114,182,0.7)',
                borderColor: 'rgba(244,114,182,1)',
                borderWidth: 1,
                borderRadius: 6,
            }],
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } },
    });

    const applyAnnualSeries = (chart, payload) => {
        if (!chart) return;
        chart.data.labels = (payload && payload.labels) || [];
        chart.data.datasets[0].data = (payload && payload.values) || [];
        chart.update();
    };

    const getBarPayload = (config, key) => {
        if (!config || !config.series || !key) {
            return { labels: [], datasets: [{ label: 'Capaian (%)', data: [] }] };
        }
        return config.series[key] || { labels: [], datasets: [{ label: 'Capaian (%)', data: [] }] };
    };

    const progressCtx = document.getElementById('targetProgressChart')?.getContext('2d');
    if (progressCtx && progressData) {
        new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: progressData.labels || [],
                datasets: [
                    {
                        label: 'Target Ayat',
                        data: progressData.target || [],
                        backgroundColor: 'rgba(59,130,246,0.4)',
                        borderColor: 'rgba(59,130,246,1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Realisasi Ayat',
                        data: progressData.actual || [],
                        backgroundColor: 'rgba(16,185,129,0.6)',
                        borderColor: 'rgba(16,185,129,1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    const monthlyCtx = document.getElementById('monthlyPercentageChart')?.getContext('2d');
    const semesterCtx = document.getElementById('semesterPercentageChart')?.getContext('2d');
    const annualCtx = document.getElementById('annualPercentageChart')?.getContext('2d');

    const monthlyConfig = (percentageSeries && percentageSeries.monthly) || {};
    const semesterConfig = (percentageSeries && percentageSeries.semester) || {};
    const annualConfig = (percentageSeries && percentageSeries.annual) || {};

    const monthlyChart = monthlyCtx ? buildBarChart(monthlyCtx, getBarPayload(monthlyConfig, monthlyConfig.current_key)) : null;
    const semesterChart = semesterCtx ? buildBarChart(semesterCtx, getBarPayload(semesterConfig, semesterConfig.current_key)) : null;
    const annualChart = annualCtx ? buildAnnualChart(annualCtx, (annualConfig && annualConfig.current) || { labels: [], values: [] }) : null;

    const bindSeriesDropdown = (menuId, chart, config, labelId) => {
        const menu = document.getElementById(menuId);
        const labelSpan = document.querySelector(`#${labelId} .period-label`);
        if (!menu || !chart || !labelSpan || !config.series) return;
        menu.querySelectorAll('button[data-key]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                if (!key) return;
                labelSpan.textContent = btn.dataset.label || btn.textContent || labelSpan.textContent;
                applyBarSeries(chart, config.series[key]);
            });
        });
    };

    const bindAnnualDropdown = (menuId, chart, labelId) => {
        const menu = document.getElementById(menuId);
        const labelSpan = document.querySelector(`#${labelId} .period-label`);
        if (!menu || !chart || !labelSpan) return;
        menu.querySelectorAll('button[data-period]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const period = btn.dataset.period;
                const payload = annualConfig[period];
                if (!payload) return;
                labelSpan.textContent = btn.textContent;
                applyAnnualSeries(chart, payload);
            });
        });
    };

    bindSeriesDropdown('monthlyPeriodSelect', monthlyChart, monthlyConfig, 'monthlyPeriodLabel');
    bindSeriesDropdown('semesterPeriodSelect', semesterChart, semesterConfig, 'semesterPeriodLabel');
    bindAnnualDropdown('annualPeriodSelect', annualChart, 'annualPeriodLabel');

    const coverageWrapper = document.querySelector('[data-coverage-endpoint]');
    const coverageModal = document.getElementById('coverageDetailModal');
    const coverageTitle = document.getElementById('coverageDetailTitle');
    const coverageBody = document.getElementById('coverageDetailBody');

    if (coverageWrapper && coverageModal && coverageTitle && coverageBody) {
        const endpointTpl = coverageWrapper.dataset.coverageEndpoint;
        const fetchDetail = async (santriId) => {
            const url = endpointTpl.replace('__SANTRI__', encodeURIComponent(santriId));
            const response = await fetch(url);
            if (!response.ok) throw new Error('gagal');
            return response.json();
        };
        coverageWrapper.querySelectorAll('[data-coverage-detail]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const santriId = btn.dataset.coverageDetail;
                const santriName = btn.dataset.santriName || 'Santri';
                coverageTitle.textContent = `Detail Hafalan - ${santriName}`;
                coverageBody.innerHTML = '<p class="text-sm text-slate-500">Memuat data...</p>';
                coverageModal.showModal();
                try {
                    const payload = await fetchDetail(santriId);
                    if (!payload.detail || !payload.detail.length) {
                        coverageBody.innerHTML = '<p class="text-sm text-slate-500">Belum ada detail hafalan.</p>';
                        return;
                    }
                    coverageBody.innerHTML = payload.detail.map(item => `
                        <div class="border border-slate-100 dark:border-slate-700 rounded-xl p-3 mb-2">
                            <div class="font-semibold text-slate-800 dark:text-slate-100">${item.surah}</div>
                            <div class="text-sm text-slate-500">Ayat ${item.ayat_start} - ${item.ayat_end}</div>
                            <div class="text-xs text-slate-400">Setor: ${item.tanggal_setor}</div>
                        </div>
                    `).join('');
                } catch (error) {
                    coverageBody.innerHTML = '<p class="text-sm text-red-500">Gagal memuat detail.</p>';
                }
            });
        });
    }

    const plannerJuz = document.getElementById('plannerJuz');
    const plannerSurahStart = document.getElementById('plannerSurahStart');
    const plannerSurahEnd = document.getElementById('plannerSurahEnd');
    const plannerAyatStart = document.getElementById('plannerAyatStart');
    const plannerAyatEnd = document.getElementById('plannerAyatEnd');
    const previewBtn = document.getElementById('previewTargetRange');
    const previewSummary = document.getElementById('targetPreviewSummary');

    const cachedSurah = {};

    const renderSurahOptions = (rows = []) => {
        const base = ['<option value="">-- Pilih Surah --</option>'];
        const options = rows.map((row) => {
            const start = row.ayat_awal || 1;
            const end = row.ayat_akhir || row.ayat_awal || 1;
            return `<option value="${row.surah_id}" data-start="${start}" data-end="${end}">(${String(row.surah_id).padStart(3,'0')}) ${row.nama_latin}</option>`;
        });
        const html = [...base, ...options].join('');
        plannerSurahStart.innerHTML = html;
        plannerSurahEnd.innerHTML = html;
    };

    const updateAyatBounds = () => {
        const startOption = plannerSurahStart?.selectedOptions[0];
        const endOption = plannerSurahEnd?.selectedOptions[0];
        if (startOption && plannerAyatStart) {
            const min = parseInt(startOption.dataset.start || '1', 10);
            const max = parseInt(startOption.dataset.end || plannerAyatStart.value || '1', 10);
            plannerAyatStart.min = min;
            plannerAyatStart.max = max;
        }
        if (endOption && plannerAyatEnd) {
            const min = parseInt(endOption.dataset.start || '1', 10);
            const max = parseInt(endOption.dataset.end || plannerAyatEnd.value || '1', 10);
            plannerAyatEnd.min = min;
            plannerAyatEnd.max = max;
        }
    };

    const applyOldSelections = () => {
        if (targetOld.surah_start_id) {
            plannerSurahStart.value = targetOld.surah_start_id;
        }
        if (targetOld.surah_end_id) {
            plannerSurahEnd.value = targetOld.surah_end_id;
        }
        updateAyatBounds();
    };

    const loadSurahByJuz = async (juz) => {
        if (!juz) {
            renderSurahOptions();
            return;
        }
        if (cachedSurah[juz]) {
            renderSurahOptions(cachedSurah[juz]);
            applyOldSelections();
            return;
        }
        const url = suratEndpointTpl.replace('__JUZ__', encodeURIComponent(juz));
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Gagal memuat surah');
            const payload = await response.json();
            cachedSurah[juz] = Array.isArray(payload) ? payload : [];
            renderSurahOptions(cachedSurah[juz]);
            applyOldSelections();
        } catch (error) {
            console.error(error);
            renderSurahOptions();
        }
    };

    plannerJuz?.addEventListener('change', (event) => {
        loadSurahByJuz(event.target.value);
    });

    plannerSurahStart?.addEventListener('change', updateAyatBounds);
    plannerSurahEnd?.addEventListener('change', updateAyatBounds);

    previewBtn?.addEventListener('click', async () => {
        if (!plannerJuz?.value || !plannerSurahStart?.value || !plannerSurahEnd?.value) {
            previewSummary.textContent = 'Mohon lengkapi pilihan juz dan surah terlebih dahulu.';
            return;
        }
        const params = new URLSearchParams({
            juz: plannerJuz.value,
            surah_start_id: plannerSurahStart.value,
            surah_end_id: plannerSurahEnd.value,
            ayat_start: plannerAyatStart?.value || '',
            ayat_end: plannerAyatEnd?.value || '',
        });
        previewSummary.textContent = 'Menghitung ringkasan...';
        try {
            const response = await fetch(`${previewUrl}?${params.toString()}`);
            if (!response.ok) throw new Error('invalid range');
            const data = await response.json();
            previewSummary.textContent = `Perkiraan: ${data.total_surah} surah, ${data.total_halaman} halaman, ${data.total_ayat} ayat.`;
        } catch (error) {
            previewSummary.textContent = 'Rentang tidak valid atau gagal menghitung.';
        }
    });

    if (plannerJuz?.value) {
        loadSurahByJuz(plannerJuz.value);
    }
});
</script>


{{-- ==============================
Penjelasan
- Statistik atas menampilkan total halaqoh, santri, dan hafalan.
- Grafik bar menampilkan jumlah hafalan per halaqoh secara real-time dari tabel hafalan_quran.
- Menggunakan warna ungu (Tailwind indigo/purple) agar berbeda dari dashboard guru.
- Data otomatis menyesuaikan isi database tanpa perlu ubah kode.
============================== --}}

</div>