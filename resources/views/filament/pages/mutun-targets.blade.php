@php use Illuminate\Support\Str; @endphp

<x-filament::page class="setoran-create">
    @php
        $old = [
            'halaqoh_id' => old('halaqoh_id'),
            'santri_id' => old('santri_id'),
            'tahun' => old('tahun', now()->year),
            'semester' => old('semester'),
            'kitab' => old('kitab'),
            'mutun_start_id' => old('mutun_start_id'),
            'mutun_end_id' => old('mutun_end_id'),
        ];
        $kitabMap = collect($mutunData ?? [])->toArray();
        $viewer = auth()->user();
        $roleAliases = [
            'koordinator_tahfizh_putra' => 'koor_tahfizh_putra',
            'koordinator_tahfizh_putri' => 'koor_tahfizh_putri',
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
                <p class="setoran-create__eyebrow">Perencanaan Mutun</p>
                <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                    Target Hafalan Mutun Tahunan
                </h1>
                <p class="text-sm md:text-base text-white/85 max-w-2xl">
                    Pilih santri, tetapkan rentang mutun di dalam satu kitab, dan pantau realisasi setoran di dashboard tahfizh mutun.
                </p>
                <div class="flex flex-wrap gap-3 text-sm font-semibold">
                    <span class="setoran-create__badge">Peran: {{ $roleLabel }}</span>
                    <span class="setoran-create__badge">Kitab Aktif: {{ count($mutunData ?? []) }}</span>
                    <span class="setoran-create__badge">Target Aktif: {{ $mutunTargets->count() }}</span>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 setoran-panel space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Form Target Mutun</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-300">Rentang mutun otomatis dibuat target per nomor.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="planner-alert">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('filament.admin.pages.mutun-targets.store') }}" class="planner-form space-y-6" id="mutunTargetForm">
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
                                @foreach ($yearOptions as $tahun)
                                    <option value="{{ $tahun }}" @selected($old['tahun'] == $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                            @error('tahun') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Semester</label>
                            <select name="semester" class="setoran-select" required>
                                <option value="">-- Pilih Semester --</option>
                                @foreach ($semesterOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($old['semester'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('semester') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="field-label">Kitab</label>
                        <select name="kitab" id="kitabSelect" class="setoran-select" required>
                            <option value="">-- Pilih Kitab --</option>
                            @foreach (($mutunData ?? []) as $kitab => $data)
                                <option value="{{ $kitab }}" @selected($old['kitab'] === $kitab)>
                                    {{ $kitab }} ({{ $data['count'] ?? 0 }} halaman)
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-300" id="kitabInfo">
                            Pilih kitab terlebih dahulu untuk melihat daftar halaman.
                        </p>
                        @error('kitab') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="planner-form-grid">
                        <div class="form-control">
                            <label class="field-label">Mutun Awal</label>
                            <select name="mutun_start_id" id="mutunStartSelect" class="setoran-select" required disabled>
                                <option value="">-- Pilih Mutun --</option>
                            </select>
                            @error('mutun_start_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-control">
                            <label class="field-label">Mutun Akhir</label>
                            <select name="mutun_end_id" id="mutunEndSelect" class="setoran-select" required disabled>
                                <option value="">-- Pilih Mutun --</option>
                            </select>
                            @error('mutun_end_id') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="planner-range-preview" id="rangeSummary">
                        Belum ada rentang yang dipilih.
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="setoran-primary-btn">
                            Simpan Target
                            <x-heroicon-o-check class="w-5 h-5" />
                        </button>
                        <button type="reset" class="setoran-secondary-btn" onclick="document.getElementById('mutunTargetForm').reset();">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <section class="targets-table space-y-4">
            <div class="targets-table__header">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Daftar Target Mutun</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-300">
                        Gunakan filter untuk menampilkan target berdasarkan santri, tahun, atau semester.
                    </p>
                </div>
                <form method="GET" class="targets-filter">
                    <div class="targets-filter__group">
                        <label class="targets-filter__label">Santri</label>
                        <select name="filter_santri" class="targets-filter__select" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($santriOptions as $santri)
                                <option value="{{ $santri->id }}" @selected($filters['santri_id'] == $santri->id)>
                                    {{ $santri->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="targets-filter__group">
                        <label class="targets-filter__label">Tahun</label>
                        <select name="filter_tahun" class="targets-filter__select" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($yearOptions as $tahun)
                                <option value="{{ $tahun }}" @selected($filters['tahun'] == $tahun)>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="targets-filter__group">
                        <label class="targets-filter__label">Semester</label>
                        <select name="filter_semester" class="targets-filter__select" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($semesterOptions as $key => $label)
                                <option value="{{ $key }}" @selected($filters['semester'] === $key)>{{ $label }}</option>
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
                                <th>Mutun Awal</th>
                                <th>Mutun Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mutunTargetGroups as $group)
                                <tr>
                                    <td class="font-semibold">{{ $group['santri'] }}</td>
                                    <td>{{ $group['tahun'] }}</td>
                                    <td>{{ Str::headline($group['semester'] ?? '-') }}</td>
                                    <td>{{ $group['kitab'] }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-900 dark:text-white">
                                            Mutun {{ $group['mutun_awal']['nomor'] ?? '-' }}
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-300">
                                            {{ $group['mutun_awal']['judul'] ?? '-' }}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900 dark:text-white">
                                            Mutun {{ $group['mutun_akhir']['nomor'] ?? '-' }}
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-300">
                                            {{ $group['mutun_akhir']['judul'] ?? '-' }}
                                        </p>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($mutunTargetGroups->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-500 dark:text-slate-300">
                                        Belum ada target mutun yang tercatat.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const kitabSelect = document.getElementById('kitabSelect');
            const startSelect = document.getElementById('mutunStartSelect');
            const endSelect = document.getElementById('mutunEndSelect');
            const kitabInfo = document.getElementById('kitabInfo');
            const rangeSummary = document.getElementById('rangeSummary');
            const kitabMap = @json($kitabMap);
            const blockedMap = @json($blockedMutunMap ?? []);
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

            const renderOptions = (select, items, kitabLabel = null) => {
                select.innerHTML = '<option value="">-- Pilih Mutun --</option>';
                const blocked = new Set(getBlockedIds());
                let availableCount = 0;
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.dataset.nomor = item.nomor ?? item.id;
                    const isBlocked = blocked.has(item.id);
                    const labelBase = kitabLabel || kitabSelect.value || 'Mutun';
                    option.textContent = `${labelBase} - Halaman ${item.nomor ?? '?'}` + (isBlocked ? ' (sudah ditargetkan)' : '');
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
                rangeSummary.textContent = `Kitab ${kitab} • Halaman ${startNomor} s/d Halaman ${endNomor}`;
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
                kitabInfo.textContent = kitab ? `${kitab} memiliki ${data.count ?? 0} halaman.` : 'Pilih kitab terlebih dahulu untuk melihat daftar halaman.';
                renderOptions(startSelect, data.items ?? [], kitab);
                renderOptions(endSelect, data.items ?? [], kitab);
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
                renderOptions(endSelect, filtered, kitab);
                endSelect.disabled = false;
                endSelect.value = '';
                updateRangeSummary();
            });

            endSelect.addEventListener('change', updateRangeSummary);

            // Prefill old state if available
            if (oldState.halaqoh_id && halaqohMap[oldState.halaqoh_id]) {
                renderSantriOptions(halaqohMap[oldState.halaqoh_id]);
                halaqohSelect.value = oldState.halaqoh_id;
                santriSelect.value = oldState.santri_id ?? '';
                santriSelect.disabled = false;
            }

            if (oldState.kitab && kitabMap[oldState.kitab]) {
                kitabSelect.value = oldState.kitab;
                kitabSelect.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    startSelect.value = oldState.mutun_start_id ?? '';
                    startSelect.dispatchEvent(new Event('change'));
                    setTimeout(() => {
                        endSelect.value = oldState.mutun_end_id ?? '';
                        updateRangeSummary();
                    }, 100);
                }, 100);
            }
        });
    </script>

    <style>
        :root {
            --planner-bg: linear-gradient(135deg, #4c1d95, #9333ea, #2563eb);
            --planner-muted: #64748b;
            --planner-panel: rgba(255,255,255,0.95);
            --planner-text: #0f172a;
        }

        html.dark {
            --planner-muted: #cbd5f5;
            --planner-panel: rgba(15,23,42,0.85);
            --planner-text: #e2e8f0;
        }

        .planner-layout {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .planner-hero {
            border-radius: 2rem;
            padding: 2rem;
            background: var(--planner-bg);
            color: #fff;
            box-shadow: 0 25px 60px rgba(76,29,149,0.35);
        }

        .setoran-create__eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.4em;
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .setoran-create__badge {
            border-radius: 999px;
            background: rgba(255,255,255,0.15);
            padding: 0.35rem 0.85rem;
        }

        .setoran-panel {
            border-radius: 1.75rem;
            padding: 1.75rem;
            background: var(--planner-panel);
            box-shadow: 0 18px 45px rgba(15,23,42,0.08);
            color: var(--planner-text);
        }

        .planner-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .form-control {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .field-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--planner-muted);
        }

        .setoran-select {
            border-radius: 1rem;
            border: 1px solid rgba(15,23,42,0.15);
            padding: 0.7rem 0.85rem;
            font-weight: 600;
            background: rgba(255,255,255,0.9);
            color: var(--planner-text);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            background-repeat: no-repeat;
            padding-right: 1.5rem;
        }

        .setoran-select:disabled {
            opacity: 0.5;
        }

        html.dark .setoran-select {
            background: rgba(15,23,42,0.65);
            border-color: rgba(148,163,184,0.3);
        }

        .field-error {
            color: #f87171;
            font-size: 0.8rem;
        }

        .planner-alert {
            border-radius: 1rem;
            padding: 0.85rem 1rem;
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.4);
            color: #059669;
            font-weight: 600;
        }

        .planner-range-preview {
            border-radius: 1.25rem;
            padding: 1rem 1.25rem;
            border: 1px dashed rgba(15,23,42,0.15);
            background: rgba(248,250,252,0.9);
            font-weight: 600;
            color: var(--planner-text);
        }

        html.dark .planner-range-preview {
            background: rgba(15,23,42,0.65);
            border-color: rgba(148,163,184,0.3);
        }

        .planner-help__item {
            padding-bottom: 0.85rem;
        }

        .planner-help__item + .planner-help__item {
            border-top: 1px solid rgba(148,163,184,0.2);
            padding-top: 0.85rem;
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

        .targets-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .targets-filter__group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .targets-filter__label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: var(--planner-muted);
        }

        .targets-filter__select {
            border-radius: 999px;
            border: 1px solid rgba(15,23,42,0.15);
            padding: 0.45rem 0.9rem;
            font-weight: 600;
            background: rgba(255,255,255,0.95);
            color: var(--planner-text);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            padding-right: 1.5rem;
        }

        .targets-filter__select::-ms-expand {
            display: none;
        }

        html.dark .targets-filter__select {
            border-color: rgba(148,163,184,0.35);
            background: rgba(15,23,42,0.65);
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
            background: linear-gradient(135deg, #7c3aed, #2563eb, #0ea5e9);
            color: #fff;
            box-shadow: 0 18px 30px rgba(124,58,237,0.35);
        }

        .setoran-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 25px 45px rgba(124,58,237,0.4);
        }

        .setoran-secondary-btn {
            border: 1px solid rgba(15,23,42,0.2);
            color: var(--planner-text);
            background-color: transparent;
        }

        html.dark .setoran-secondary-btn {
            border-color: rgba(148,163,184,0.4);
        }
    </style>
</x-filament::page>
