{{-- ==============================
📖 Form Input Setoran Hafalan (Guru)
Sinkron dengan controller SetoranHafalanController
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

        <div>
            <label class="block font-semibold mb-1">Mode Input</label>
            <select name="mode" id="mode" class="form-select w-full">
                <option value="ayat" selected>Per Ayat (Surah/Ayat)</option>
                <option value="page">Per Halaman Mushaf</option>
            </select>
        </div>

        {{-- Form mode ayat --}}
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

        {{-- Mode halaman --}}
        <div id="mode-page" class="hidden">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Halaman Awal</label>
                    <input type="number" name="page_start" min="1" max="604" class="form-input w-full">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Halaman Akhir</label>
                    <input type="number" name="page_end" min="1" max="604" class="form-input w-full">
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
📜 SCRIPT INTERAKTIF FORM
=============================== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeSelect = document.getElementById('mode');
    const modeAyat = document.getElementById('mode-ayat');
    const modePage = document.getElementById('mode-page');
    const juzSelect = document.getElementById('juzSelect');
    const surahSelect = document.getElementById('surahSelect');
    const ayahStartSelect = document.getElementById('ayahStartSelect');
    const ayahEndSelect = document.getElementById('ayahEndSelect');

    let completedSetoran = [];

    // 🔁 Ubah tampilan form mode
    modeSelect.addEventListener('change', () => {
        if (modeSelect.value === 'page') {
            modeAyat.classList.add('hidden');
            modePage.classList.remove('hidden');
        } else {
            modeAyat.classList.remove('hidden');
            modePage.classList.add('hidden');
        }
    });

    // === Ambil data setoran santri (yang sudah disetor) ===
    fetch('{{ route('guru.setoran.ajax.getSetoranSantri', ['santriId' => $santri->id]) }}')
        .then(res => res.json())
        .then(data => {
            completedSetoran = data;
        })
        .catch(err => console.error('Gagal ambil data setoran:', err));

    // === Saat Juz dipilih, muat daftar surah berdasarkan database ===
    juzSelect.addEventListener('change', function() {
        const juz = this.value;
        if (!juz) return;

        fetch('{{ route('guru.setoran.ajax.getSuratByJuz', ['juz' => '']) }}' + juz)
            .then(res => res.json())
            .then(data => {
                surahSelect.innerHTML = '<option value="">-- Pilih Surah --</option>';
                data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.surah_id;
                    opt.textContent = `Surah ${s.nama_latin}`;
                    opt.dataset.ayatAwal = s.ayat_awal;
                    opt.dataset.ayatAkhir = s.ayat_akhir;
                    surahSelect.appendChild(opt);
                });
                surahSelect.dispatchEvent(new Event('change'));
            })
            .catch(err => console.error('Gagal ambil surah:', err));
    });

    // === Saat Surah dipilih, isi ayat awal/akhir ===
    surahSelect.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        if (!opt) return;

        const mulai = parseInt(opt.dataset.ayatAwal, 10);
        const akhir = parseInt(opt.dataset.ayatAkhir, 10);

        ayahStartSelect.innerHTML = '';
        ayahEndSelect.innerHTML = '';

        for (let i = mulai; i <= akhir; i++) {
            const o1 = document.createElement('option');
            o1.value = i;
            o1.textContent = i;
            ayahStartSelect.appendChild(o1);

            const o2 = document.createElement('option');
            o2.value = i;
            o2.textContent = i;
            ayahEndSelect.appendChild(o2);
        }

        ayahStartSelect.value = mulai;
        ayahEndSelect.value = mulai;
    });

    // === Pastikan ayat akhir ≥ ayat awal ===
    ayahStartSelect.addEventListener('change', () => {
        const awal = parseInt(ayahStartSelect.value || '1');
        const options = ayahEndSelect.options;
        for (const opt of options) {
            opt.disabled = parseInt(opt.value) < awal;
        }
        if (parseInt(ayahEndSelect.value) < awal) {
            ayahEndSelect.value = awal;
        }
    });
});
</script>
@endsection
