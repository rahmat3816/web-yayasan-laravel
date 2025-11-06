{{-- ==============================
📘 Edit Data Halaqoh – Admin & Operator
============================== --}}
@extends('layouts.admin')

@section('title', 'Edit Halaqoh')

@section('content')
<x-breadcrumb title="Edit Halaqoh" />

<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">✏️ Edit Data Halaqoh</h1>

    {{-- 🔁 Kembali ke Data Halaqoh --}}
    <div class="mb-4">
        <a href="{{ route('admin.halaqoh.index') }}" class="text-blue-600 hover:underline">
            ← Kembali ke Data Halaqoh
        </a>
    </div>

    {{-- ⚠️ Pesan Error Validasi --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4 dark:bg-red-900 dark:text-red-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 📝 Form Edit --}}
    <form action="{{ route('admin.halaqoh.update', $halaqoh->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Nama Halaqoh --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Halaqoh</label>
            <input type="text" name="nama_halaqoh" value="{{ old('nama_halaqoh', $halaqoh->nama_halaqoh) }}"
                class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 focus:ring focus:ring-blue-300"
                required>
        </div>

        {{-- Guru Pembimbing --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Guru Pembimbing</label>
            <select id="guru_id" name="guru_id"
                class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Guru --</option>
                @foreach ($guru as $g)
                    <option value="{{ $g->id }}" data-unit="{{ $g->unit_id }}" data-gender="{{ $g->jenis_kelamin }}"
                        {{ old('guru_id', $halaqoh->guru_id) == $g->id ? 'selected' : '' }}>
                        {{ $g->nama }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Daftar santri akan dimuat otomatis sesuai guru pembimbing.</p>
        </div>

        {{-- Unit hanya untuk Superadmin --}}
        @role('superadmin')
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Unit Pendidikan</label>
            <select name="unit_id"
                class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $halaqoh->unit_id) == $unit->id ? 'selected' : '' }}>
                        {{ $unit->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>
        @endrole

        {{-- Daftar Santri --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Pilih Santri</label>
            <div id="santri-container" class="border rounded p-3 bg-gray-50 dark:bg-gray-900 h-48 overflow-y-auto">
                <p class="text-gray-500 dark:text-gray-400 text-sm" id="loading-text">
                    Memuat daftar santri...
                </p>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Keterangan</label>
            <textarea name="keterangan"
                class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 h-24">{{ old('keterangan', $halaqoh->keterangan) }}</textarea>
        </div>

        {{-- Tombol Simpan --}}
        <div class="pt-4">
            <button type="submit"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>
</div>

{{-- ==============================
📡 AJAX: Ambil Santri Berdasarkan Guru
============================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const guruSelect = document.getElementById('guru_id');
    const santriContainer = document.getElementById('santri-container');
    const halaqohId = "{{ $halaqoh->id }}";

    function loadSantri(guruId) {
        if (!guruId) {
            santriContainer.innerHTML = '<p class="text-gray-500 text-sm">Pilih guru terlebih dahulu.</p>';
            return;
        }

        santriContainer.innerHTML = '<p class="text-blue-500 text-sm animate-pulse">🔄 Memuat daftar santri...</p>';

        fetch(`/admin/halaqoh/santri-by-guru/${guruId}?halaqoh_id=${halaqohId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    santriContainer.innerHTML = `<p class="text-red-500 text-sm">${data.error}</p>`;
                    return;
                }

                if (data.length === 0) {
                    santriContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada santri yang cocok.</p>';
                    return;
                }

                const list = data.map(santri => `
                    <label class="flex items-center space-x-2 mb-1">
                        <input type="checkbox" name="santri_ids[]" value="${santri.id}"
                            ${santri.terpilih ? 'checked' : ''} class="rounded border-gray-400">
                        <span>${santri.nama} <span class="text-xs text-gray-500">(${santri.nisy})</span></span>
                    </label>
                `).join('');

                santriContainer.innerHTML = list;
            })
            .catch(err => {
                console.error(err);
                santriContainer.innerHTML = '<p class="text-red-500 text-sm">Gagal memuat data santri.</p>';
            });
    }

    // Muat awal (guru aktif saat edit)
    if (guruSelect.value) {
        loadSantri(guruSelect.value);
    }

    // Muat ulang saat ganti guru
    guruSelect.addEventListener('change', function() {
        loadSantri(this.value);
    });
});
</script>
@endpush

@endsection
