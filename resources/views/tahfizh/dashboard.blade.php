{{-- ==============================
📘 Tahfizh Dashboard (Versi Terbaru)
Tujuan: Menampilkan data real hafalan per halaqoh menggunakan Chart.js
File: resources/views/tahfizh/dashboard.blade.php
============================== --}}

@extends('layouts.admin')
@section('content')
<x-breadcrumb />

@php
    $cards = [
        [
            'title' => 'Kelola Halaqoh',
            'description' => 'Atur guru pengampu dan santri.',
            'url' => route('tahfizh.halaqoh.index'),
            'icon' => '👥',
        ],
        [
            'title' => 'Tambah Halaqoh',
            'description' => 'Buat halaqoh baru dan tetapkan pengampu.',
            'url' => route('tahfizh.halaqoh.create'),
            'icon' => '➕',
        ],
        [
            'title' => 'Rekap Hafalan',
            'description' => 'Unduh rekap progres per halaqoh.',
            'url' => route('tahfizh.dashboard'),
            'icon' => '📈',
        ],
    ];
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
        <h3 class="text-lg font-semibold">Total Hafalan</h3>
        <p class="text-3xl font-bold">{{ $totalHafalan }}</p>
    </div>
</div>

{{-- Grafik Hafalan --}}
<div class="mt-10 grid gap-6 lg:grid-cols-2">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">📚 Hafalan per Halaqoh</h2>
        <canvas id="halaqohChart" height="200"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
        <h2 class="text-2xl font-semibold mb-4">👨‍🎓 Hafalan per Santri (Top 10)</h2>
        <canvas id="santriChart" height="200"></canvas>
    </div>
</div>

<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    @php $hasTimeline = !empty($santriTimeline['datasets']); @endphp
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold mb-1">📈 Riwayat Setoran per Santri</h2>
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
<div class="mt-10">
    <livewire:tahfizh-target-planner :gender-filter="$genderFilter" :unit-filter="$unitFilter" />
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
@endsection

{{-- ==============================
📋 Penjelasan
- Statistik atas menampilkan total halaqoh, santri, dan hafalan.
- Grafik bar menampilkan jumlah hafalan per halaqoh secara real-time dari tabel hafalan_quran.
- Menggunakan warna ungu (Tailwind indigo/purple) agar berbeda dari dashboard guru.
- Data otomatis menyesuaikan isi database tanpa perlu ubah kode.
============================== --}}
