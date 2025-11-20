<x-filament::page>
@php
    $santri = $santri ?? null;
    $targets = $targets ?? collect();
    $availableTargets = $targets->filter(fn ($target) => ($target->setorans_count ?? 0) === 0);
    $defaultTargetId = old('target_id');

    if ($defaultTargetId && ($targets->firstWhere('id', (int) $defaultTargetId)?->setorans_count ?? 0) > 0) {
        $defaultTargetId = null;
    }

    if (! $defaultTargetId) {
        $defaultTargetId = optional($availableTargets->first())->id;
    }

    $targetSelectionDisabled = $availableTargets->isEmpty();
@endphp

<div class="setoran-create space-y-8 max-w-5xl mx-auto w-full">
    <section class="setoran-create__hero mb-6">
        <div class="space-y-4">
            <p class="setoran-create__eyebrow">Input Setoran Mutun</p>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                Tambah Setoran Hafalan Mutun
            </h1>
            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                <span class="setoran-create__badge">
                    Santri: {{ $santri->nama ?? '-' }}
                </span>
                <span class="setoran-create__badge">
                    Unit: {{ $santri->unit->nama_unit ?? '-' }}
                </span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 setoran-panel space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Form Setoran Mutun</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pilih target mutun yang disetorkan lalu isi nilai penilaian.</p>
                </div>
                <a href="{{ route('filament.admin.pages.tahfizh.mutun.setoran') }}"
                   class="setoran-secondary-btn">
                    Kembali ke Daftar
                </a>
            </div>

            <x-admin.alert />

            <form method="POST"
                  action="{{ route('filament.admin.pages.mutun-setorans.store') }}"
                  class="space-y-6">
                @csrf
                <input type="hidden" name="santri_id" value="{{ $santri->id }}">

                <div class="planner-form-grid">
                    <div class="form-control span-2">
                        <label class="field-label">Target Mutun</label>
                        <select name="target_id"
                                class="setoran-select"
                                @disabled($targetSelectionDisabled)
                                required>
                            @foreach ($targets as $target)
                                @php
                                    $isCompleted = ($target->setorans_count ?? 0) > 0;
                                    $mutun = $target->mutun;
                                    $nomor = $mutun?->nomor ?? $mutun?->urutan ?? $target->id;
                                @endphp
                                <option value="{{ $target->id }}"
                                        @selected((int) $defaultTargetId === $target->id)
                                        @disabled($isCompleted)>
                                    Mutun {{ $nomor }} — {{ $mutun?->judul ?? '-' }} ({{ $mutun?->kitab ?? '-' }})
                                    @if($isCompleted)
                                        (Sudah setor)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if($targetSelectionDisabled)
                            <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                                Semua target mutun untuk santri ini telah disetorkan.
                            </p>
                        @endif
                        @error('target_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-control">
                        <label class="field-label">Tanggal Setor</label>
                        <input type="date"
                               name="tanggal"
                               class="setoran-input"
                               value="{{ old('tanggal', now()->toDateString()) }}"
                               required>
                        @error('tanggal') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-control">
                        <label class="field-label">Penilai</label>
                        <input type="text"
                               class="setoran-input"
                               value="{{ auth()->user()?->name }}"
                               readonly>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Nilai Mutqin (1-10)</label>
                    <select name="nilai_mutqin" class="setoran-select" required>
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" @selected((int) old('nilai_mutqin', 8) === $i)>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                    @error('nilai_mutqin') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">Catatan</label>
                    <textarea name="catatan"
                              class="setoran-textarea"
                              rows="4"
                              placeholder="Catatan tambahan penilaian...">{{ old('catatan') }}</textarea>
                    @error('catatan') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="setoran-primary-btn">
                        Simpan Setoran
                        <x-heroicon-o-check class="w-5 h-5" />
                    </button>
                    <a href="{{ route('filament.admin.pages.tahfizh.mutun.setoran') }}"
                       class="setoran-secondary-btn">
                        Batalkan
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

@push('styles')
    <style>
        :root {
            --form-bg: rgba(255,255,255,0.96);
            --form-border: rgba(15,23,42,0.08);
            --form-text: #0f172a;
            --form-muted: #64748b;
            --form-input-bg: rgba(249,250,251,0.98);
            --form-hero: linear-gradient(135deg, #1d4ed8, #7c3aed, #a855f7);
        }

        html.dark {
            --form-bg: rgba(2,6,23,0.92);
            --form-border: rgba(148,163,184,0.35);
            --form-text: #e2e8f0;
            --form-muted: rgba(226,232,240,0.7);
            --form-input-bg: rgba(15,23,42,0.7);
            --form-hero: linear-gradient(140deg, #020617, #0f172a 40%, #7c3aed);
        }

        .setoran-create__hero {
            border-radius: 2rem;
            padding: 2rem;
            background: radial-gradient(circle at top, rgba(255,255,255,0.2), transparent 45%),
                var(--form-hero);
            color: #fff;
            box-shadow: 0 30px 70px rgba(124,58,237,0.35);
            margin-bottom: 2rem;
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
        }

        .setoran-panel,
        .setoran-sidebar {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            border-radius: 1.75rem;
            padding: 1.75rem;
            box-shadow: 0 25px 60px rgba(15,23,42,0.08);
            color: var(--form-text);
        }

        .planner-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .field-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--form-muted);
            margin-bottom: 0.35rem;
        }

        .setoran-input,
        .setoran-textarea,
        .setoran-select {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(15,23,42,0.12);
            background-color: var(--form-input-bg);
            color: var(--form-text);
            padding: 0.85rem 1rem;
            font-weight: 500;
        }

        .setoran-select option {
            color: #0f172a;
        }

        html.dark .setoran-select option {
            color: #e2e8f0;
            background-color: #111827;
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

        .setoran-secondary-btn {
            border: 1px solid rgba(15,23,42,0.2);
            color: var(--form-text);
            background-color: transparent;
        }

        html.dark .setoran-secondary-btn {
            border-color: rgba(148,163,184,0.4);
        }

        .field-error {
            margin-top: 0.35rem;
            color: #dc2626;
            font-size: 0.85rem;
        }

        .sidebar-target {
            border-radius: 1.2rem;
            border: 1px solid rgba(15,23,42,0.08);
            padding: 1rem;
            background: rgba(248,250,252,0.9);
        }

        .sidebar-target--done {
            background: rgba(34,197,94,0.08);
            border-color: rgba(34,197,94,0.4);
        }

        .sidebar-tag {
            border-radius: 999px;
            padding: 0.2rem 0.75rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            background: rgba(34,197,94,0.15);
            color: #15803d;
        }

        .sidebar-tag--pending {
            background: rgba(248,113,113,0.15);
            color: #b91c1c;
        }
    </style>
@endpush

</x-filament::page>
