@php use Illuminate\Support\Str; @endphp

<x-filament::page class="setoran-create">
    @php
        $old = [
            'halaqoh_id' => old('halaqoh_id'),
            'santri_id' => old('santri_id'),
            'tahun' => old('tahun', now()->year),
            'semester' => old('semester'),
            'status' => old('status', 'berjalan'),
            'kitab' => old('kitab'),
            'hadits_start_id' => old('hadits_start_id'),
            'hadits_end_id' => old('hadits_end_id'),
        ];
        $kitabMap = collect($kitabData ?? [])->toArray();
        $viewer = auth()->user();
        $roleAliases = [
            'koordinator_tahfizh_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfizh_putri' => 'koor_tahfizh_putri',
            'koordinator_tahfiz_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfiz_putri' => 'koor_tahfizh_putri',
        ];
        $rolePriorities = [
            'kabag_kesantrian_putra',
            'kabag_kesantrian_putri',
            'koor_tahfizh_putra',
            'koor_tahfizh_putri',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
        ];
        $rolePool = collect($viewer?->roles?->pluck('name')->toArray() ?? [])
            ->merge($viewer?->jabatans?->pluck('slug')->toArray() ?? [])
            ->map(fn ($role) => $roleAliases[strtolower($role)] ?? strtolower($role))
            ->unique();
        $primaryRole = collect($rolePriorities)->first(fn ($role) => $rolePool->contains($role));
        $roleLabel = $primaryRole ? Str::upper($primaryRole) : 'ROLE TIDAK DITEMUKAN';
        $filters = $filterState ?? ['santri_id' => null, 'tahun' => null, 'semester' => null];
    @endphp

    <div class="planner-layout space-y-8 max-w-5xl mx-auto w-full">
        <section class="setoran-create__hero planner-hero">
            <div class="space-y-3">
                <p class="setoran-create__eyebrow">Perencanaan Hadits</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                    Target Hafalan Hadits Tahunan
                </h1>
                <p class="text-sm md:text-base text-white/85 max-w-2xl">
                    Pilih santri sesuai halaqoh, tetapkan rentang hadits dalam satu kitab, dan monitor progresnya di halaman rekap hadits.
                </p>
                <div class="flex flex-wrap gap-3 text-sm font-semibold">
                    <span class="setoran-create__badge">Peran: {{ $roleLabel }}</span>
                    <span class="setoran-create__badge">Kitab Aktif: {{ count($kitabData ?? []) }}</span>
                    <span class="setoran-create__badge">Target Aktif: {{ $haditsTargets->count() }}</span>
    </div>
        </section>

        <div class="grid gap-6 lg:gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 setoran-panel space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Form Target Hadits</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-300">Rentang hadits otomatis dibuat target per nomor hadits.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="planner-alert">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('filament.admin.pages.hadits-targets.store') }}" class="planner-form space-y-6" id="haditsTargetForm">
                    @csrf
                    <div class="planner-form-grid">
                        <div class="form-control">
                            <label class="field-label">Halaqoh</label>
                            <select name="halaqoh_id" id="halaqohSelect" class="setoran-select" required>
                                <option value="">-- Pilih Halaqoh --</option>
                                @foreach (($halaqohOptions ?? collect()) as $halaqoh)
                                    <option value="{{ $halaqoh['id'] }}" @selected($old['halaqoh_id'] == $halaqoh['id'])>
                                        {{ $halaqoh['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('halaqoh_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Santri</label>
                            <select name="santri_id" id="santriSelect" class="setoran-select" required disabled>
                                <option value="">-- Pilih Santri --</option>
                            </select>
                            @error('santri_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Tahun</label>
                            <select name="tahun" class="setoran-select" required>
                                <option value="">-- Pilih Tahun --</option>
                                @foreach ($yearOptions ?? [] as $year)
                                    <option value="{{ $year }}" @selected($old['tahun'] == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('tahun') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Semester</label>
                            <select name="semester" class="setoran-select" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach ($semesterOptions ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected($old['semester'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('semester') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="planner-form-grid">
                        <div class="form-control span-2">
                            <label class="field-label">Kitab Hadits</label>
                            <select name="kitab" id="kitabSelect" class="setoran-select" required>
                                <option value="">-- Pilih Kitab --</option>
                                @foreach ($kitabData ?? [] as $kitab => $data)
                                    <option value="{{ $kitab }}" data-count="{{ $data['count'] ?? 0 }}" @selected($old['kitab'] === $kitab)>
                                        {{ $kitab }} ({{ $data['count'] ?? 0 }} hadits)
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1" id="kitabInfo">Pilih kitab untuk melihat daftar hadits di dalamnya.</p>
                            @error('kitab') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Hadits Awal</label>
                            <select name="hadits_start_id" id="haditsStartSelect" class="setoran-select" required disabled>
                                <option value="">-- Pilih Hadits Awal --</option>
                            </select>
                            @error('hadits_start_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Hadits Akhir</label>
                            <select name="hadits_end_id" id="haditsEndSelect" class="setoran-select" required disabled>
                                <option value="">-- Pilih Hadits Akhir --</option>
                            </select>
                            @error('hadits_end_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="planner-preview flex flex-wrap gap-3 items-center">
                        <button type="button" class="setoran-secondary-btn" disabled>
                            Rentang Otomatis
                        </button>
                        <p class="planner-preview__text" id="rangeSummary">Belum ada rentang yang dipilih.</p>
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <a href="{{ url()->current() }}" class="setoran-secondary-btn gap-2">
                            <x-heroicon-o-arrow-uturn-left class="h-4 w-4" />
                            Reset
                        </a>
                        <button type="submit" class="setoran-primary-btn gap-2">
                            <x-heroicon-o-check class="h-4 w-4" />
                            Simpan Target
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (($haditsTargetGroups ?? collect())->count())
            <section class="setoran-card targets-table">
                <div class="targets-table__header">
                    <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Daftar Target Hadits</h3>
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="targets-filter__label">Santri</label>
                            <select name="filter_santri" class="targets-filter__select" onchange="this.form.submit()">
                                <option value="">Semua Santri</option>
                                @foreach ($santriOptions as $santri)
                                    <option value="{{ $santri->id }}" @selected((string) $filters['santri_id'] === (string) $santri->id)>
                                        {{ $santri->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="targets-filter__label">Tahun</label>
                            <select name="filter_tahun" class="targets-filter__select" onchange="this.form.submit()">
                                <option value="">Semua Tahun</option>
                                @foreach ($yearOptions ?? [] as $year)
                                    <option value="{{ $year }}" @selected((string) $filters['tahun'] === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="targets-filter__label">Semester</label>
                            <select name="filter_semester" class="targets-filter__select" onchange="this.form.submit()">
                                <option value="">Semua Semester</option>
                                @foreach (($semesterOptions ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['semester'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="targets-table__body">
                    <div class="targets-table__content">
                        <table>
                            <thead>
                                <tr>
                                    <th>Santri</th>
                                    <th>Tahun</th>
                                    <th>Semester</th>
                                    <th>Kitab</th>
                                    <th>Hadits Awal</th>
                                    <th>Hadits Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($haditsTargetGroups as $group)
                                    <tr>
                                        <td class="font-semibold">{{ $group['santri'] }}</td>
                                        <td>{{ $group['tahun'] }}</td>
                                        <td>{{ Str::headline($group['semester'] ?? '-') }}</td>
                                        <td>{{ $group['kitab'] }}</td>
                                        <td>
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                Hadits {{ $group['hadits_awal']['nomor'] ?? '-' }}
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-300">
                                                {{ $group['hadits_awal']['judul'] ?? '-' }}
                                            </p>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                Hadits {{ $group['hadits_akhir']['nomor'] ?? '-' }}
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-300">
                                                {{ $group['hadits_akhir']['judul'] ?? '-' }}
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const kitabSelect = document.getElementById('kitabSelect');
            const startSelect = document.getElementById('haditsStartSelect');
            const endSelect = document.getElementById('haditsEndSelect');
            const kitabInfo = document.getElementById('kitabInfo');
            const rangeSummary = document.getElementById('rangeSummary');
            const kitabMap = @json($kitabMap);
            const blockedMap = @json($blockedHaditsMap ?? []);
            const halaqohMap = @json(($halaqohOptions ?? collect())->mapWithKeys(fn ($halaqoh) => [$halaqoh['id'] => $halaqoh['santri']])->toArray());
            const oldState = @json($old);
            const halaqohSelect = document.getElementById('halaqohSelect');
            const santriSelect = document.getElementById('santriSelect');
            const tahunSelect = document.querySelector('select[name="tahun"]');
            const semesterSelect = document.querySelector('select[name="semester"]');
            const getBlockedIds = () => {
                const santriId = santriSelect?.value;
                const tahun = tahunSelect?.value;
                const semester = semesterSelect?.value;
                const kitab = kitabSelect.value;
                if (!santriId || !tahun || !semester || !kitab) {
                    return [];
                }

                const key = `${santriId}-${tahun}-${semester}-${kitab}`;
                return blockedMap[key] ?? [];
            };
            const renderOptions = (select, items) => {
                select.innerHTML = '<option value=\"\">-- Pilih Hadits --</option>';
                const blocked = new Set(getBlockedIds());
                let availableCount = 0;
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.dataset.nomor = item.nomor ?? item.id;
                    const isBlocked = blocked.has(item.id);
                    option.textContent = `Hadits ${item.nomor ?? '?'}` + ' - ' + `${item.judul}` + (isBlocked ? ' (sudah ditargetkan)' : '');
                    option.disabled = isBlocked;
                    if (!isBlocked) {
                        availableCount++;
                    }
                    select.appendChild(option);
                });
                select.disabled = availableCount === 0;
            };

            const updateRangeSummary = () => {
                const kitab = kitabSelect.value || '-';
                const startOption = startSelect.selectedOptions[0];
                const endOption = endSelect.selectedOptions[0];
                if (!kitab || !startOption || !endOption || !startOption.value || !endOption.value) {
                    rangeSummary.textContent = 'Belum ada rentang yang dipilih.';
                    return;
                }
                const startNomor = startOption.dataset.nomor;
                const endNomor = endOption.dataset.nomor;
                rangeSummary.textContent = `Kitab ${kitab} • Hadits ${startNomor} s/d Hadits ${endNomor}`;
            };

            const renderSantriOptions = (options = []) => {
                santriSelect.innerHTML = '<option value="">-- Pilih Santri --</option>';
                options.forEach((santri) => {
                    const opt = document.createElement('option');
                    opt.value = santri.id;
                    opt.textContent = santri.nama;
                    santriSelect.appendChild(opt);
                });
                santriSelect.disabled = options.length === 0;
            };

            halaqohSelect.addEventListener('change', () => {
                const selected = halaqohSelect.value;
                const santriList = halaqohMap[selected] ?? [];
                renderSantriOptions(santriList);
                santriSelect.value = '';
                if (!selected) {
                    santriSelect.disabled = true;
                }
            });

            [santriSelect, tahunSelect, semesterSelect].forEach(field => {
                field?.addEventListener('change', () => {
                    if (kitabSelect.value) {
                        kitabSelect.dispatchEvent(new Event('change'));
                    }
                });
            });

            kitabSelect.addEventListener('change', () => {
                const kitab = kitabSelect.value;
                const data = kitabMap[kitab] ?? {count: 0, items: []};
                kitabInfo.textContent = kitab ? `${kitab} memiliki ${data.count ?? 0} hadits.` : 'Pilih kitab terlebih dahulu untuk melihat daftar hadits.';
                renderOptions(startSelect, data.items ?? []);
                renderOptions(endSelect, data.items ?? []);
                startSelect.disabled = !kitab;
                endSelect.disabled = !kitab;
                startSelect.value = '';
                endSelect.value = '';
                updateRangeSummary();
            });

            startSelect.addEventListener('change', () => {
                const startNomor = parseInt(startSelect.selectedOptions[0]?.dataset.nomor ?? 0, 10);
                const kitab = kitabSelect.value;
                const data = kitabMap[kitab] ?? {items: []};
                const filtered = (data.items ?? []).filter(item => (item.nomor ?? 0) >= startNomor);
                renderOptions(endSelect, filtered);
                endSelect.disabled = false;
                endSelect.value = '';
                updateRangeSummary();
            });

            endSelect.addEventListener('change', updateRangeSummary);

            if (halaqohSelect && santriSelect) {
                if (oldState.halaqoh_id && halaqohMap[oldState.halaqoh_id]) {
                    halaqohSelect.value = oldState.halaqoh_id;
                    renderSantriOptions(halaqohMap[oldState.halaqoh_id] ?? []);
                    santriSelect.disabled = false;
                    if (oldState.santri_id) {
                        santriSelect.value = oldState.santri_id;
                    }
                } else {
                    santriSelect.disabled = true;
                }
            }

            if (kitabSelect.value) {
                kitabSelect.dispatchEvent(new Event('change'));
                if (oldState.hadits_start_id) {
                    startSelect.value = oldState.hadits_start_id;
                    startSelect.dispatchEvent(new Event('change'));
                }
                if (oldState.hadits_end_id) {
                    endSelect.value = oldState.hadits_end_id;
                }
                updateRangeSummary();
            }

        });
    </script>
    <style>
        :root {
            --planner-bg: rgba(255,255,255,0.92);
            --planner-border: rgba(15,23,42,0.08);
            --planner-text: #0f172a;
            --planner-muted: rgba(100,116,139,0.95);
            --planner-input-bg: rgba(248,250,252,0.95);
            --planner-hero: linear-gradient(140deg, #312e81, #1d4ed8 40%, #0ea5e9);
        }

        html.dark {
            --planner-bg: rgba(15,23,42,0.75);
            --planner-border: rgba(99,102,241,0.25);
            --planner-text: rgba(226,232,240,0.98);
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

        .setoran-select option[disabled] {
            color: rgba(148,163,184,0.85);
            font-style: italic;
        }

        .setoran-select:focus {
            outline: 2px solid rgba(99,102,241,0.4);
            outline-offset: 2px;
        }

        .planner-preview__text {
            font-size: 0.9rem;
            min-width: 240px;
            color: var(--planner-muted);
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

        .targets-filter__label {
            font-size: 0.75rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--planner-muted);
        }

        .targets-filter__select {
            border-radius: 1rem;
            border: 1px solid rgba(99,102,241,0.35);
            background: var(--planner-input-bg);
            padding: 0.6rem 0.85rem;
            color: var(--planner-text);
            font-weight: 600;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: none !important;
            padding-right: 1.5rem;
        }

        .targets-filter__select::-ms-expand {
            display: none;
        }

        html.dark .targets-filter__select {
            border-color: rgba(148,163,184,0.35);
        }

        .targets-table__header {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        @media (min-width: 768px) {
            .targets-table__header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .targets-table__body {
            border-radius: 1.5rem;
            border: 1px solid rgba(15,23,42,0.08);
            background: rgba(255,255,255,0.9);
        }

        html.dark .targets-table__body {
            background: rgba(15,23,42,0.75);
            border-color: rgba(148,163,184,0.2);
        }

        .targets-table__content {
            overflow-x: auto;
        }

        .targets-table__content table {
            width: 100%;
            border-collapse: collapse;
        }

        .targets-table__content thead th {
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: var(--planner-muted);
            padding: 0.85rem 0.75rem;
            border-bottom: 1px solid rgba(15,23,42,0.08);
        }

        .targets-table__content tbody td {
            padding: 0.95rem 0.75rem;
            border-bottom: 1px solid rgba(15,23,42,0.08);
        }

        html.dark .targets-table__content thead th,
        html.dark .targets-table__content tbody td {
            border-color: rgba(148,163,184,0.2);
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

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.85rem 0.75rem;
            border-bottom: 1px solid rgba(15,23,42,0.08);
        }

        html.dark .table th,
        html.dark .table td {
            border-color: rgba(148,163,184,0.18);
        }

        .table th {
            text-align: left;
            font-size: 0.7rem;
            color: var(--planner-muted);
            letter-spacing: 0.25em;
            text-transform: uppercase;
        }

        .hidden {
            display: none !important;
        }
    </style>
</x-filament::page>
