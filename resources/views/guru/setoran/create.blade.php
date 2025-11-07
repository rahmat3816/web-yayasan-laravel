{{-- ==============================
📖 Form Input Setoran Hafalan (Guru)
Berbasis data_quran.json (tanpa mode halaman)
============================== --}}

@extends('layouts.app')
@section('title', 'Tambah Setoran Hafalan')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">➕ Tambah Setoran Hafalan Quran</h1>

    {{-- ⚠️ Pesan error --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong class="font-semibold">Terjadi kesalahan:</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('guru.setoran.store', ['santriId' => $santri->id]) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold mb-1">Nama Santri</label>
            <input type="text" class="form-input w-full bg-gray-100" value="{{ $santri->nama }}" readonly>
        </div>

        <div>
            <label class="block font-semibold mb-1">Tanggal Setor</label>
            <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', now()->toDateString()) }}" required class="form-input w-full">
        </div>

        {{-- Mode ayat --}}
        <div id="mode-ayat">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Juz</label>
                    <select name="juz_start" id="juzSelect" class="form-select w-full" required>
                        <option value="">-- Pilih Juz --</option>
                        @for ($i = 1; $i <= 30; $i++)
                            <option value="{{ $i }}">Juz {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Surah</label>
                    <select name="surah_id" id="surahSelect" class="form-select w-full" required>
                        <option value="">-- Pilih Surah --</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block font-semibold mb-1">Ayat Awal</label>
                    <select name="ayah_start" id="ayahStartSelect" class="form-select w-full" required>
                        <option value="">-- Pilih Ayat --</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Ayat Akhir</label>
                    <select name="ayah_end" id="ayahEndSelect" class="form-select w-full" required>
                        <option value="">-- Pilih Ayat --</option>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1">Status Penilaian</label>
            <select name="status" class="form-select w-full">
                <option value="lulus">Lulus</option>
                <option value="ulang">Ulang</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold mb-1">Catatan</label>
            <textarea name="catatan" rows="3" class="form-textarea w-full">{{ old('catatan') }}</textarea>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="{{ route('guru.setoran.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">⬅️ Kembali</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">💾 Simpan</button>
        </div>
    </form>
</div>

{{-- ===============================
📜 SCRIPT INTERAKTIF FORM (JSON)
=============================== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const juzSelect = document.getElementById('juzSelect');
    const surahSelect = document.getElementById('surahSelect');
    const ayahStartSelect = document.getElementById('ayahStartSelect');
    const ayahEndSelect = document.getElementById('ayahEndSelect');

    const suratByJuzUrlTpl = @json(route('guru.setoran.ajax.getSuratByJuz', ['juz' => '__JUZ__']));
    const getSetoranSantriUrl = @json(route('guru.setoran.ajax.getSetoranSantri', ['santriId' => $santri->id]));

    let completedSetoran = [];

    // === Ambil data setoran santri (yang sudah disetor) ===
    fetch(getSetoranSantriUrl)
        .then(res => res.json())
        .then(data => { completedSetoran = data || []; })
        .catch(() => { completedSetoran = []; });

    // === Saat Juz dipilih ===
    juzSelect.addEventListener('change', function() {
        const juz = parseInt(this.value);
        surahSelect.innerHTML = '<option value="">-- Pilih Surah --</option>';
        ayahStartSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        ayahEndSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        if (!juz) return;

        const url = suratByJuzUrlTpl.replace('__JUZ__', encodeURIComponent(juz));
        fetch(url)
            .then(res => res.json())
            .then(rows => {
                if (!Array.isArray(rows)) return;
                rows.forEach(r => {
                    const opt = document.createElement('option');
                    opt.value = r.surah_id;
                    opt.textContent = `(${String(r.surah_id).padStart(3,'0')}) ${r.nama_latin}`;
                    opt.dataset.ayatAwal  = r.ayat_awal;
                    opt.dataset.ayatAkhir = r.ayat_akhir;
                    surahSelect.appendChild(opt);
                });
            });
    });

    // === Saat Surah dipilih ===
    surahSelect.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        ayahStartSelect.innerHTML = '<option value="">-- Pilih Ayat --</option>';
        ayahEndSelect.innerHTML   = '<option value="">-- Pilih Ayat --</option>';
        if (!opt) return;
        const mulai = parseInt(opt.dataset.ayatAwal, 10);
        const akhir = parseInt(opt.dataset.ayatAkhir, 10);
        for (let i = mulai; i <= akhir; i++) {
            ayahStartSelect.innerHTML += `<option value="${i}">${i}</option>`;
            ayahEndSelect.innerHTML += `<option value="${i}">${i}</option>`;
        }
        ayahStartSelect.value = mulai;
        ayahEndSelect.value = mulai;
    });
});
</script>
@endsection
