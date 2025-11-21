<x-filament::page class="setoran-page">
    <div class="setoran-wrapper space-y-10 max-w-6xl mx-auto w-full">
        <section class="setoran-hero rekap-hero mutun-hero">
            <div class="setoran-hero__content w-full">
                <div class="space-y-3">
                    <p class="setoran-eyebrow">Keamanan</p>
                    <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">Catat Pelanggaran Santri</h1>
                    <p class="text-sm md:text-base text-white/85 max-w-2xl">
                        Pilih santri, pilih jenis pelanggaran atau ketaatan, poin terisi otomatis, dan simpan untuk mengakumulasi SP.
                    </p>
                </div>
            </div>
            <div class="hero-legend">
                <div>
                    <p class="text-white font-semibold">Ambang SP</p>
                    <p class="text-xs text-white/70">SP1: 100 • SP2: 200 • SP3: 300 / langsung berat</p>
                </div>
                <div>
                    <p class="text-white font-semibold">Tip Cepat</p>
                    <p class="text-xs text-white/70">Cek ketaatan untuk mengurangi poin ≥ 200.</p>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="planner-alert">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('filament.admin.pages.keamanan.catat-pelanggaran') }}" class="planner-form space-y-6">
            @csrf
            <div class="setoran-card form-shell space-y-6">
                <div class="form-header">
                    <div>
                        <p class="text-sm text-white/70">Form Pelanggaran</p>
                        <h3 class="text-xl font-semibold text-white">Input Pelanggaran Santri</h3>
                        <p class="text-xs text-white/60">Pilih santri dan pelanggaran, poin otomatis terisi.</p>
                    </div>
                    <a href="{{ route('filament.admin.pages.keamanan.rekap-pelanggaran') }}" class="setoran-secondary-btn btn-ghost pill-btn">Kembali ke Rekap</a>
                </div>
                <div class="planner-form-grid form-gap">
                    <div class="form-control">
                        <label class="field-label">Santri</label>
                        <select name="santri_id" class="setoran-select" required>
                            <option value="">-- Pilih Santri --</option>
                            @foreach ($santriOptions as $santri)
                                <option value="{{ $santri->id }}" @selected(old('santri_id', $defaultSantriId) == $santri->id)>
                                    {{ $santri->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('santri_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-control">
                        <label class="field-label">Kategori Pelanggaran</label>
                        <select id="kategoriSelect" class="setoran-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriOptions as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="field-label">Jenis Pelanggaran</label>
                        <select name="pelanggaran_type_id" id="pelanggaranSelect" class="setoran-select" required>
                            <option value="">-- Pilih Pelanggaran --</option>
                            @foreach ($pelanggaranOptions as $opt)
                                <option value="{{ $opt['id'] }}" data-poin="{{ $opt['poin'] ?? 0 }}" data-kat="{{ $opt['kategori_id'] }}">
                                    {{ $opt['nama'] }} ({{ $opt['kategori'] ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('pelanggaran_type_id') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-control">
                        <label class="field-label">Poin</label>
                        <input type="number" name="poin" id="poinInput" class="setoran-input" value="{{ old('poin') }}" min="0" required>
                        @error('poin') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-control">
                        <label class="field-label">Tanggal</label>
                        <input type="date" name="tanggal" class="setoran-input" value="{{ old('tanggal', now()->toDateString()) }}" required>
                        @error('tanggal') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Catatan</label>
                    <textarea name="catatan" class="setoran-textarea" rows="4" placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                    @error('catatan') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-1">
                    <button type="submit" class="setoran-primary-btn pill-btn action-btn">
                        <x-heroicon-o-check class="h-5 w-5" />
                        Simpan Pelanggaran
                    </button>
                    <button type="reset" class="setoran-secondary-btn pill-btn ghost-btn">Reset</button>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <style>
            .planner-form-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 1.25rem;
            }
            .form-gap > * {
                margin-top: 0.15rem;
            }
            .planner-alert {
                border-radius: 1rem;
                padding: 0.85rem 1rem;
                background: rgba(34,197,94,0.12);
                border: 1px solid rgba(34,197,94,0.4);
                color: #059669;
                font-weight: 600;
            }
            .rekap-hero {
                background: radial-gradient(120% 120% at 0% 0%, rgba(59,130,246,0.18), rgba(14,165,233,0.16)),
                            linear-gradient(120deg, #0b1225 0%, #112449 60%, #0b1225 100%);
            }
            .hero-legend {
                margin-top: 1rem;
                padding: 0.9rem;
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 0.9rem;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 0.75rem;
            }
            .form-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .btn-ghost {
                background: transparent;
                border: 1px solid rgba(255,255,255,0.16);
                color: #fff;
            }
            .pill-btn {
                border-radius: 999px;
                padding-inline: 1rem;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                height: 42px;
            }
            .action-btn {
                background: linear-gradient(135deg, #0ea5e9, #2563eb, #7c3aed);
                box-shadow: 0 12px 30px rgba(79,70,229,0.4);
            }
            .ghost-btn {
                background: rgba(255,255,255,0.04);
                border: 1px solid rgba(255,255,255,0.12);
                color: #e2e8f0;
                backdrop-filter: blur(3px);
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
            }
            .setoran-card.form-shell {
                background: linear-gradient(150deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
                border: 1px solid rgba(255,255,255,0.07);
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 10px 35px rgba(0,0,0,0.25);
            }
            .mutun-hero {
                border-radius: 1.25rem;
                background: radial-gradient(160% 160% at 15% 15%, rgba(168,85,247,0.35), rgba(14,165,233,0.12), rgba(15,23,42,0.95)),
                            linear-gradient(135deg, #111827, #312e81 45%, #9333ea 90%);
                box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            }
            .setoran-select,
            .setoran-input,
            .setoran-textarea {
                background: #0f172a;
                border: 1px solid rgba(255,255,255,0.12);
                color: #fff;
                border-radius: 0.75rem;
                padding: 0.65rem 0.85rem;
                width: 100%;
            }
            .setoran-select {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: calc(100% - 0.75rem) center;
                padding-right: 2.5rem;
            }
            .setoran-input, .setoran-textarea, .setoran-select {
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
            }
        </style>
    @endpush

@push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const select = document.getElementById('pelanggaranSelect');
                const poin = document.getElementById('poinInput');
                const kategori = document.getElementById('kategoriSelect');

                select?.addEventListener('change', () => {
                    const opt = select.selectedOptions[0];
                    if (opt && opt.dataset.poin) {
                        poin.value = opt.dataset.poin;
                    }
                });

                kategori?.addEventListener('change', () => {
                    const kat = kategori.value;
                    Array.from(select.options).forEach((opt) => {
                        if (!opt.value) return; // skip placeholder
                        if (!kat) {
                            opt.hidden = false;
                        } else {
                            opt.hidden = opt.dataset.kat !== kat;
                        }
                    });
                    // reset selection if current option tidak cocok
                    const current = select.selectedOptions[0];
                    if (current && current.hidden) {
                        select.value = '';
                        poin.value = '';
                    }
                });
            });
        </script>
    @endpush
    @include('filament.pages.partials.setoran-styles')
</x-filament::page>
