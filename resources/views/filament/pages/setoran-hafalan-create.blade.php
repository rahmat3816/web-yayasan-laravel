<x-filament::page>
@php
    $santri = $santri ?? null;
    $halaqoh = $halaqoh ?? null;
@endphp

<div class="setoran-create space-y-8 max-w-5xl mx-auto w-full">
    <section class="setoran-create__hero">
        <div class="space-y-4">
            <p class="setoran-create__eyebrow">Input Setoran</p>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight text-white">
                Tambah Setoran Hafalan Quran
            </h1>
            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                <span class="setoran-create__badge">
                    Santri: {{ $santri->nama ?? '-' }}
                </span>
                <span class="setoran-create__badge">
                    Halaqoh: {{ $halaqoh->nama_halaqoh ?? '-' }}
                </span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 setoran-panel space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Form Setoran</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pastikan nilai diisi sesuai hasil penilaian lapangan.</p>
                </div>
                <a href="{{ route('filament.admin.pages.tahfizh.setoran-hafalan') }}"
                   class="setoran-secondary-btn">
                    Kembali ke Daftar
                </a>
            </div>

            <x-admin.alert />

            <form method="POST"
                  action="{{ route('filament.admin.pages.setoran-hafalan.store', ['santri' => $santri->id]) }}"
                  class="space-y-6">
                @csrf

                <div class="field-group">
                    <label class="field-label">Nama Santri</label>
                    <input type="text" class="setoran-input" value="{{ $santri->nama }}" readonly>
                </div>

                <div class="field-group">
                    <label class="field-label">Tanggal Setor</label>
                    <input type="date"
                           name="tanggal_setor"
                           value="{{ old('tanggal_setor', now()->toDateString()) }}"
                           required
                           class="setoran-input">
                </div>

                <div id="mode-ayat" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="field-group">
                            <label class="field-label">Juz</label>
                            <select name="juz_start" id="juzSelect" class="setoran-select" required>
                                <option value="">-- Pilih Juz --</option>
                                @for ($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}">{{ 'Juz '.$i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Surah</label>
                            <select name="surah_id" id="surahSelect" class="setoran-select" required>
                                <option value="">-- Pilih Surah --</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="field-group">
                            <label class="field-label">Ayat Awal</label>
                            <select name="ayah_start" id="ayahStartSelect" class="setoran-select" required>
                                <option value="">-- Pilih Ayat --</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Ayat Akhir</label>
                            <select name="ayah_end" id="ayahEndSelect" class="setoran-select" required>
                                <option value="">-- Pilih Ayat --</option>
                            </select>
                        </div>
                    </div>
                </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="field-group">
                            <label class="field-label">Tajwid</label>
                            <select name="penilaian_tajwid" class="setoran-select" required>
                                <option value="">-- Pilih Nilai --</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_tajwid') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Mutqin (1-10)</label>
                            <select name="penilaian_mutqin" class="setoran-select" required>
                                <option value="">-- Pilih Skor --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_mutqin') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Adab</label>
                            <select name="penilaian_adab" class="setoran-select" required>
                                <option value="">-- Pilih Nilai --</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_adab') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                <div class="field-group">
                    <label class="field-label">Catatan</label>
                    <textarea name="catatan"
                              rows="3"
                              class="setoran-textarea"
                              placeholder="Tambahkan catatan penilaian bila diperlukan.">{{ old('catatan') }}</textarea>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <a href="{{ route('filament.admin.pages.tahfizh.setoran-hafalan') }}" class="setoran-secondary-btn">
                        Batalkan
                    </a>
                    <button type="submit" class="setoran-primary-btn">
                        Simpan Setoran
                    </button>
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
        --form-hero: linear-gradient(135deg, #0ea5e9, #2563eb, #7c3aed);
    }

    html.dark {
        --form-bg: rgba(2,6,23,0.92);
        --form-border: rgba(148,163,184,0.35);
        --form-text: #e2e8f0;
        --form-muted: rgba(226,232,240,0.7);
        --form-input-bg: rgba(15,23,42,0.7);
        --form-hero: linear-gradient(140deg, #020617, #0f172a 40%, #1d4ed8);
    }

    .setoran-create__hero {
        border-radius: 2rem;
        padding: 2rem;
        background: radial-gradient(circle at top, rgba(255,255,255,0.2), transparent 45%),
            var(--form-hero);
        color: #fff;
        box-shadow: 0 30px 70px rgba(14,165,233,0.35);
    }

    .setoran-create__eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.4em;
        font-size: 0.75rem;
        color: rgba(255,255,255,0.8);
        font-weight: 600;
    }

    .setoran-create {
        padding-top: 0.5rem;
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

    .setoran-panel {
        margin-top: 1.25rem;
    }

    .setoran-panel h2,
    .setoran-sidebar h3 {
        color: var(--form-text);
    }

    .setoran-panel p,
    .setoran-sidebar p,
    .setoran-sidebar dt {
        color: var(--form-muted);
    }

    .setoran-sidebar dd {
        color: var(--form-text);
    }

    .sidebar-note {
        padding: 1rem;
        border-radius: 1.25rem;
        background: rgba(226,232,240,0.6);
        color: var(--form-muted);
    }

    html.dark .sidebar-note {
        background: rgba(15,23,42,0.8);
        color: var(--form-muted);
    }

    .setoran-panel form > * + * {
        margin-top: 1.5rem;
    }

    .setoran-sidebar > * + * {
        margin-top: 1.25rem;
    }

    .field-label {
        display: block;
        font-weight: 600;
        color: var(--form-muted);
        margin-bottom: 0.35rem;
    }

    .setoran-input,
    .setoran-select,
    .setoran-textarea {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(15,23,42,0.12);
        background-color: var(--form-input-bg);
        color: var(--form-text);
        padding: 0.85rem 1rem;
        font-weight: 500;
        box-shadow: inset 0 0 0 rgba(0,0,0,0);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    html.dark .setoran-input,
    html.dark .setoran-select,
    html.dark .setoran-textarea {
        border-color: rgba(148,163,184,0.25);
    }

    .setoran-input:focus,
    .setoran-select:focus,
    .setoran-textarea:focus {
        outline: none;
        border-color: rgba(14,165,233,0.6);
        box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
    }

    .setoran-select {
        appearance: none;
        background-image: linear-gradient(45deg, transparent 50%, rgba(15,23,42,0.5) 50%),
            linear-gradient(135deg, rgba(15,23,42,0.5) 50%, transparent 50%);
        background-position: calc(100% - 20px) calc(1em + 6px), calc(100% - 15px) calc(1em + 6px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
    }

    html.dark .setoran-select {
        background-image: linear-gradient(45deg, transparent 50%, rgba(226,232,240,0.7) 50%),
            linear-gradient(135deg, rgba(226,232,240,0.7) 50%, transparent 50%);
    }

    .setoran-textarea {
        resize: vertical;
    }

    .setoran-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb, #7c3aed);
        color: #fff;
        padding: 0.8rem 1.75rem;
        font-weight: 600;
        box-shadow: 0 18px 30px rgba(37,99,235,0.35);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .setoran-primary-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 25px 45px rgba(37,99,235,0.4);
    }

    .setoran-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border: 1px solid rgba(15,23,42,0.2);
        color: var(--form-text);
        background-color: transparent;
    }

    html.dark .setoran-secondary-btn {
        border-color: rgba(148,163,184,0.4);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const suratByJuzUrlTpl = @json(route('filament.admin.pages.tahfizh-dashboard.surat-by-juz', ['juz' => '__JUZ__']));
    const getSetoranSantriUrl = @json(route('filament.admin.pages.setoran-hafalan.ajax-santri', ['santri' => $santri->id]));

    const juzSelect = document.getElementById('juzSelect');
    const surahSelect = document.getElementById('surahSelect');
    const ayahStartSelect = document.getElementById('ayahStartSelect');
    const ayahEndSelect = document.getElementById('ayahEndSelect');

    let completedSetoran = [];

    fetch(getSetoranSantriUrl)
        .then(response => response.json())
        .then(data => {
            completedSetoran = Array.isArray(data) ? data : [];
        })
        .catch(() => { completedSetoran = []; });

    juzSelect.addEventListener('change', async function() {
        const juz = parseInt(this.value);
        surahSelect.innerHTML = '<option value="">-- Pilih Surah --</option>';
        ayahStartSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        ayahEndSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        if (!juz) return;

        const url = suratByJuzUrlTpl.replace('__JUZ__', encodeURIComponent(juz));
        const res = await fetch(url);
        const rows = await res.json();
        if (!Array.isArray(rows)) return;

        const setoranJuz = completedSetoran.filter(s => parseInt(s.juz) === juz);

        rows.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.surah_id;
            opt.textContent = `(${String(r.surah_id).padStart(3,'0')}) ${r.nama_latin}`;
            opt.dataset.ayatAwal  = r.ayat_awal;
            opt.dataset.ayatAkhir = r.ayat_akhir;

            const lastSetoran = setoranJuz.find(s => parseInt(s.surat_akhir) === r.surah_id);
            const ayatTerakhir = lastSetoran ? parseInt(lastSetoran.ayat_akhir) : 0;

            if (ayatTerakhir >= r.ayat_akhir) {
                opt.disabled = true;
                opt.classList.add('text-gray-400');
                opt.textContent += ' (Selesai)';
            }

            surahSelect.appendChild(opt);
        });

        const semuaSelesai = rows.every(r => {
            const last = setoranJuz.find(s => parseInt(s.surat_akhir) === r.surah_id);
            const akhirSetor = last ? parseInt(last.ayat_akhir) : 0;
            return akhirSetor >= r.ayat_akhir;
        });
        if (semuaSelesai) {
            juzSelect.classList.add('bg-gray-200', 'text-gray-500');
            alert(`Juz ${juz} sudah selesai seluruhnya`);
            juzSelect.value = '';
        }
    });

    surahSelect.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        ayahStartSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        ayahEndSelect.innerHTML   = '<option value="">-- Pilih Ayat --</option>';
        if (!opt) return;

        const mulai = parseInt(opt.dataset.ayatAwal, 10);
        const akhir = parseInt(opt.dataset.ayatAkhir, 10);
        const juzTerpilih = parseInt(juzSelect.value);

        const lastSetoran = completedSetoran.find(
            s => s.juz == juzTerpilih && s.surat_akhir == parseInt(opt.value)
        );
        const batasTerakhir = lastSetoran ? parseInt(lastSetoran.ayat_akhir, 10) : 0;

        for (let i = mulai; i <= akhir; i++) {
            const disabled = i <= batasTerakhir;
            const attr = disabled ? 'disabled class="text-gray-400 bg-gray-100 line-through"' : '';
            ayahStartSelect.innerHTML += `<option value="${i}" ${attr}>${i}${disabled ? ' (Sudah)' : ''}</option>`;
            ayahEndSelect.innerHTML += `<option value="${i}" ${attr}>${i}${disabled ? ' (Sudah)' : ''}</option>`;
        }

        const firstAvailable = Array.from(ayahStartSelect.options).find(o => !o.disabled && o.value);
        if (firstAvailable) {
            ayahStartSelect.value = firstAvailable.value;
            ayahEndSelect.value = firstAvailable.value;
        } else {
            alert('Seluruh ayat pada surah ini sudah selesai.');
            surahSelect.value = '';
        }
    });
});
</script>
@endpush
</x-filament::page>

