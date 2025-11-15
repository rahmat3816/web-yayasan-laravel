@extends('layouts.app')

@section('title', 'Tambah Setoran Hafalan')

@section('content')
<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="card glass bg-gradient-to-r from-sky-100 via-cyan-50 to-white text-gray-900 shadow-xl">
            <div class="card-body">
                <p class="text-sm uppercase tracking-[0.4em] text-sky-600">Input Setoran</p>
                <h1 class="text-3xl font-semibold text-gray-900">Tambah Setoran Hafalan Quran</h1>
                <p class="mt-2 text-gray-600">
                    Formulir ini menggunakan logika data Quran (JSON) yang sudah baku. Silakan isi tanpa melakukan perubahan terhadap mekanisme penilaian.
                </p>
                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                    <span class="badge badge-outline border-sky-500 text-sky-700">
                        Santri: {{ $santri->nama }}
                    </span>
                    <span class="badge badge-outline border-sky-500 text-sky-700">
                        Halaqoh: {{ optional($santri->halaqoh)->nama_halaqoh ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card bg-white/95 text-gray-900 shadow-xl border border-emerald-50/80 rounded-3xl">
            <div class="card-body space-y-4">
                <x-admin.alert />

                <form method="POST" action="{{ route('guru.setoran.store', ['santriId' => $santri->id]) }}" class="space-y-6">
                    @csrf

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold text-slate-600">Nama Santri</span>
                        </label>
                        <input type="text" class="input input-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" value="{{ $santri->nama }}" readonly>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold text-slate-600">Tanggal Setor</span>
                        </label>
                        <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', now()->toDateString()) }}" required class="input input-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    </div>

                    <div id="mode-ayat" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-slate-600">Juz</span>
                                </label>
                                <select name="juz_start" id="juzSelect" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                    <option value="">-- Pilih Juz --</option>
                                    @for ($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}">Juz {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-slate-600">Surah</span>
                                </label>
                                <select name="surah_id" id="surahSelect" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                    <option value="">-- Pilih Surah --</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-slate-600">Ayat Awal</span>
                                </label>
                                <select name="ayah_start" id="ayahStartSelect" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                    <option value="">-- Pilih Ayat --</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-slate-600">Ayat Akhir</span>
                                </label>
                                <select name="ayah_end" id="ayahEndSelect" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                    <option value="">-- Pilih Ayat --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-slate-600">Tajwid</span>
                            </label>
                            <select name="penilaian_tajwid" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                <option value="">-- Pilih Nilai --</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_tajwid') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-slate-600">Mutqin (1-10)</span>
                            </label>
                            <select name="penilaian_mutqin" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                <option value="">-- Pilih Skor --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_mutqin') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-slate-600">Adab</span>
                            </label>
                            <select name="penilaian_adab" class="select select-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" required>
                                <option value="">-- Pilih Nilai --</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected((int) old('penilaian_adab') === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold text-slate-600">Catatan</span>
                        </label>
                        <textarea name="catatan" rows="3" class="textarea textarea-bordered bg-white text-gray-900 border border-gray-200 shadow-sm focus:ring-2 focus:ring-primary/20 focus:border-primary" placeholder="Catatan pengampu (opsional)">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <a href="{{ route('guru.setoran.index') }}" class="btn btn-ghost gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const juzSelect = document.getElementById('juzSelect');
    const surahSelect = document.getElementById('surahSelect');
    const ayahStartSelect = document.getElementById('ayahStartSelect');
    const ayahEndSelect = document.getElementById('ayahEndSelect');

    const suratByJuzUrlTpl = @json(route('guru.setoran.ajax.getSuratByJuz', ['juz' => '__JUZ__']));
    const getSetoranSantriUrl = @json(route('guru.setoran.ajax.getSetoranSantri', ['santriId' => $santri->id]));

    let completedSetoran = [];

    fetch(getSetoranSantriUrl)
        .then(res => res.json())
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
@endsection
