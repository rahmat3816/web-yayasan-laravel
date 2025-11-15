<x-filament::page>
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
    $suratEndpoint = route('filament.admin.pages.tahfizh-dashboard.surat-by-juz', ['juz' => '__JUZ__']);
@endphp

<div class="setoran-create planner-layout space-y-8 max-w-5xl mx-auto w-full">
    <section class="setoran-create__hero planner-hero">
        <div class="space-y-3">
            <p class="setoran-create__eyebrow">Perencanaan Tahfizh</p>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                Target Hafalan Tahunan
            </h1>
            <p class="text-sm md:text-base text-white/85 max-w-2xl">
                Tetapkan rentang juz–surah–ayat untuk tiap santri, lihat ringkasan otomatis, lalu simpan agar terhubung dengan panel setoran.
            </p>
            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                <span class="setoran-create__badge">Peran: Koordinator Tahfizh</span>
                <span class="setoran-create__badge">Rentang: Juz → Surah → Ayat</span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 setoran-panel space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Form Target Hafalan</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-300">Pastikan rentang juz dan ayat sesuai kemampuan santri.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="planner-alert">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('tahfizh.target.store') }}" class="planner-form space-y-6" id="targetPlanningForm">
                @csrf
                <div class="planner-form-grid">
                    <div class="form-control span-2">
                        <label class="field-label">Santri</label>
                        <select name="santri_id" class="setoran-select" required>
                            <option value="">-- Pilih Santri --</option>
                            @foreach ($targetSantriOptions as $santri)
                                <option value="{{ $santri->id }}" @selected($targetOld['santri_id'] == $santri->id)>{{ $santri->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="field-label">Tahun Target</label>
                        <select name="tahun" class="setoran-select" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($targetYearOptions as $tahun)
                                <option value="{{ $tahun }}" @selected($targetOld['tahun'] == $tahun)>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="field-label">Juz</label>
                        <select name="juz" id="plannerJuz" class="setoran-select" required>
                            <option value="">-- Pilih Juz --</option>
                            @for ($i = 1; $i <= 30; $i++)
                                <option value="{{ $i }}" @selected($targetOld['juz'] == $i)>Juz {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="planner-form-grid">
                    <div class="form-control">
                        <label class="field-label">Surah Awal</label>
                        <select name="surah_start_id" id="plannerSurahStart" class="setoran-select" data-old="{{ $targetOld['surah_start_id'] }}" required>
                            <option value="">-- Pilih Surah --</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="field-label">Surah Akhir</label>
                        <select name="surah_end_id" id="plannerSurahEnd" class="setoran-select" data-old="{{ $targetOld['surah_end_id'] }}" required>
                            <option value="">-- Pilih Surah --</option>
                        </select>
                    </div>
                </div>

                <div class="planner-form-grid">
                    <div class="form-control">
                        <label class="field-label">Ayat Awal</label>
                        <select name="ayat_start" id="plannerAyatStart" class="setoran-select" required>
                            <option value="">-- Pilih Ayat Awal --</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="field-label">Ayat Akhir</label>
                        <select name="ayat_end" id="plannerAyatEnd" class="setoran-select" required>
                            <option value="">-- Pilih Ayat Akhir --</option>
                        </select>
                    </div>
                </div>

                <div class="planner-preview flex flex-wrap gap-3 items-center">
                    <button type="button" class="setoran-secondary-btn" id="previewTargetRange">Pratinjau Ringkasan</button>
                    <p class="planner-preview__text" id="targetPreviewSummary">Belum ada ringkasan.</p>
                </div>

                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <a href="{{ url()->current() }}" class="setoran-secondary-btn gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7m-7 7h18"/></svg>
                        Reset
                    </a>
                    <button type="submit" class="setoran-primary-btn gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                        Simpan Target
                    </button>
                </div>
            </form>
        </div>

        <aside class="setoran-sidebar planner-sidebar space-y-5">
            <div class="sidebar-note">
                Simpan target untuk mengaktifkan monitoring pada halaman rekap dan laporan tahunan.
            </div>

            <div class="planner-meta">
                <span class="planner-meta__label">Target aktif</span>
                <span class="planner-meta__value">{{ $hafalanTargets->count() }} santri</span>
            </div>
        </aside>
    </div>

    @if ($hafalanTargets->count())
        <div class="targets-card">
            <div class="targets-card__header">
                <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Daftar Target Aktif</h3>
                <p class="text-sm text-slate-500 dark:text-slate-300">Data ditampilkan berdasarkan entri terbaru.</p>
            </div>
            <div class="targets-card__body">
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
        </div>
    @endif
</div>

@push('styles')
    <style>
        :root {
            --planner-bg: rgba(255,255,255,0.95);
            --planner-border: rgba(148,163,184,0.2);
            --planner-text: #0f172a;
            --planner-muted: #64748b;
            --planner-input-bg: rgba(249,250,251,0.98);
            --planner-hero: linear-gradient(135deg, #14b8a6, #0ea5e9, #2563eb);
        }

        html.dark {
            --planner-bg: rgba(15,23,42,0.92);
            --planner-border: rgba(148,163,184,0.35);
            --planner-text: #e2e8f0;
            --planner-muted: rgba(226,232,240,0.7);
            --planner-input-bg: rgba(15,23,42,0.7);
            --planner-hero: linear-gradient(140deg, #020617, #0f172a 35%, #0ea5e9);
        }

        .planner-layout {
            padding: 2rem 0 3.5rem;
            width: 100%;
        }

        @media (max-width: 768px) {
            .planner-layout {
                padding: 1.25rem 0 2.5rem;
            }
        }

        .planner-layout > * + * {
            margin-top: clamp(1.75rem, 4vw, 2.75rem);
        }

        .setoran-create__hero {
            border-radius: 2rem;
            padding: 2rem;
            background: radial-gradient(circle at top, rgba(255,255,255,0.2), transparent 45%), var(--planner-hero);
            box-shadow: 0 30px 70px rgba(14,165,233,0.35);
        }

        .setoran-create__eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.4em;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.8);
            font-weight: 600;
        }

        .setoran-create__badge {
            padding: 0.35rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.65);
            background-color: rgba(255,255,255,0.15);
            color: #fff;
        }

        .setoran-panel,
        .setoran-sidebar {
            background: var(--planner-bg);
            border: 1px solid var(--planner-border);
            border-radius: 1.75rem;
            padding: 1.75rem;
            box-shadow: 0 25px 60px rgba(15,23,42,0.08);
            color: var(--planner-text);
        }

        @media (max-width: 1024px) {
            .setoran-panel,
            .setoran-sidebar {
                padding: 1.5rem;
            }
        }

        .setoran-panel p,
        .setoran-sidebar p,
        .planner-preview__text {
            color: var(--planner-muted);
        }

        .planner-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .planner-form-grid .span-2 {
                grid-column: span 2;
            }
        }

        .planner-form .form-control {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--planner-muted);
            margin-bottom: 0.35rem;
        }

        .setoran-select {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(15,23,42,0.12);
            background-color: var(--planner-input-bg);
            color: var(--planner-text);
            padding: 0.85rem 1rem;
            font-weight: 500;
        }

        .setoran-select:focus {
            outline: 2px solid rgba(99,102,241,0.4);
            outline-offset: 2px;
        }

        .planner-preview__text {
            font-size: 0.9rem;
            min-width: 240px;
        }

        .planner-alert {
            border-radius: 1rem;
            padding: 0.85rem 1.25rem;
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.35);
            color: #047857;
            font-weight: 600;
        }

        .sidebar-note {
            padding: 1rem;
            border-radius: 1.25rem;
            background: rgba(226,232,240,0.6);
            color: var(--planner-muted);
        }

        html.dark .sidebar-note {
            background: rgba(15,23,42,0.8);
        }

        .planner-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 1.25rem;
            padding: 1rem 1.25rem;
            border: 1px dashed rgba(99,102,241,0.35);
        }

        .planner-meta__label {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.7rem;
            color: var(--planner-muted);
        }

        .planner-meta__value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--planner-text);
        }

        .setoran-primary-btn,
        .setoran-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            gap: 0.5rem;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .setoran-primary-btn {
            background: linear-gradient(135deg, #0ea5e9, #2563eb, #7c3aed);
            color: #fff;
            box-shadow: 0 18px 30px rgba(37,99,235,0.35);
        }

        .setoran-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 25px 45px rgba(37,99,235,0.4);
        }

        .setoran-secondary-btn {
            border: 1px solid rgba(15,23,42,0.2);
            color: var(--planner-text);
            background-color: transparent;
        }

        html.dark .setoran-secondary-btn {
            border-color: rgba(148,163,184,0.4);
        }

        .targets-card {
            margin-top: 1.5rem;
            border-radius: 1.75rem;
            border: 1px solid rgba(99, 102, 241, 0.18);
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,248,255,0.9));
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        html.dark .targets-card {
            background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(17,24,39,0.95));
            border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
        }

        .targets-card__header {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .targets-card__body {
            padding: 1rem 1.75rem 1.75rem;
        }

        .targets-card table {
            width: 100%;
            min-width: 720px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .targets-card table thead th {
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: rgba(15,23,42,0.7);
            padding: 0.9rem 1.25rem;
            background: rgba(148, 163, 184, 0.12);
        }

        html.dark .targets-card table thead th {
            color: rgba(226,232,240,0.75);
            background: rgba(15,23,42,0.55);
        }

        .targets-card table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            color: var(--planner-text);
        }

        html.dark .targets-card table tbody td {
            color: rgba(226,232,240,0.92);
            border-color: rgba(148,163,184,0.25);
        }

        .targets-card table tbody tr:last-child td {
            border-bottom: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const suratEndpointTpl = @json($suratEndpoint);
            const previewUrl = @json(route('filament.admin.pages.tahfizh-dashboard.preview-target'));
            const targetOld = @json($targetOld);

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
                    const max = parseInt(startOption.dataset.end || '1', 10);
                    plannerAyatStart.innerHTML = buildAyatOptions(min, max, targetOld.ayat_start);
                }
                if (endOption && plannerAyatEnd) {
                    const min = parseInt(endOption.dataset.start || '1', 10);
                    const max = parseInt(endOption.dataset.end || '1', 10);
                    plannerAyatEnd.innerHTML = buildAyatOptions(min, max, targetOld.ayat_end);
                }
            };
            const buildAyatOptions = (min, max, selectedValue) => {
                const base = ['<option value="">-- Pilih Ayat --</option>'];
                for (let i = min; i <= max; i++) {
                    base.push(`<option value="${i}" ${selectedValue == i ? 'selected' : ''}>Ayat ${i}</option>`);
                }
                return base.join('');
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
@endpush
</x-filament::page>
