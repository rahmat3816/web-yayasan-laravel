{{-- resources/views/admin/halaqoh/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Tambah Halaqoh')

@section('content')
<x-breadcrumb title="Tambah Halaqoh" />

<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">➕ Tambah Data Halaqoh</h1>

    <div class="mb-4">
        <a href="{{ route('admin.halaqoh.index') }}" class="text-blue-600 hover:underline">← Kembali ke Data Halaqoh</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4 dark:bg-red-900 dark:text-red-100">
            <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.halaqoh.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold">Nama Halaqoh</label>
            <input type="text" name="nama_halaqoh" value="{{ old('nama_halaqoh') }}"
                   class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2"
                   required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Guru Pembimbing</label>
            <select id="guru_id" name="guru_id"
                    class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2" required>
                <option value="">-- Pilih Guru --</option>
                @foreach ($guru as $g)
                    <option value="{{ $g->id }}" data-unit="{{ $g->unit_id }}" data-gender="{{ $g->jenis_kelamin }}">
                        {{ $g->nama }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Santri akan otomatis muncul sesuai unit & jenis kelamin guru.</p>
        </div>

        @role('superadmin')
        <div>
            <label class="block text-sm font-semibold">Unit Pendidikan</label>
            <select name="unit_id" class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2">
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
        </div>
        @endrole

        <div>
            <label class="block text-sm font-semibold mb-2">Pilih Santri</label>
            <div id="santri-container" class="border rounded p-3 bg-gray-50 dark:bg-gray-900 h-48 overflow-y-auto">
                <p class="text-gray-500 dark:text-gray-400 text-sm" id="loading-text">
                    Pilih guru terlebih dahulu untuk menampilkan daftar santri...
                </p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold">Keterangan</label>
            <textarea name="keterangan" class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 h-24">{{ old('keterangan') }}</textarea>
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">💾 Simpan Data</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const guruSelect = document.getElementById('guru_id');
    const santriContainer = document.getElementById('santri-container');

    // base URL (contoh: http://yayasan.test atau http://localhost/yayasan/public)
    const baseUrl = @json(url('/'));

    guruSelect.addEventListener('change', function() {
        const guruId = this.value;
        if (!guruId) {
            santriContainer.innerHTML = '<p class="text-gray-500 text-sm">Pilih guru terlebih dahulu.</p>';
            return;
        }

        santriContainer.innerHTML = '<p class="text-blue-500 text-sm animate-pulse">🔄 Memuat daftar santri...</p>';

        fetch(`${baseUrl}/admin/halaqoh/santri-by-guru/${encodeURIComponent(guruId)}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    santriContainer.innerHTML = `<p class="text-red-500 text-sm">${data.error}</p>`;
                    return;
                }
                if (!Array.isArray(data) || data.length === 0) {
                    santriContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada santri yang cocok dengan guru ini.</p>';
                    return;
                }
                santriContainer.innerHTML = data.map(s => `
                    <label class="flex items-center space-x-2 mb-1">
                        <input type="checkbox" name="santri_ids[]" value="${s.id}" class="rounded border-gray-400">
                        <span>${s.nama} <span class="text-xs text-gray-500">(${s.nisy})</span></span>
                    </label>
                `).join('');
            })
            .catch(err => {
                console.error(err);
                santriContainer.innerHTML = '<p class="text-red-500 text-sm">Gagal memuat data santri.</p>';
            });
    });
});
</script>
@endpush
