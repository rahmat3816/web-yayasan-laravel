@extends('layouts.app')

@section('title', !empty($edit) ? 'Ubah Halaqoh' : 'Buat Halaqoh')

@section('content')
<div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-2">
        <a href="{{ route('tahfizh.dashboard') }}" class="hover:underline">Tahfizh</a>
        <span class="mx-1">›</span>
        <a href="{{ route('tahfizh.halaqoh.index') }}" class="hover:underline">Halaqoh</a>
        <span class="mx-1">›</span>
        <span class="text-gray-700 font-medium">{{ !empty($edit) ? 'Ubah' : 'Buat Baru' }}</span>
    </div>

    <h1 class="text-2xl font-semibold mb-4">
        {{ !empty($edit) ? 'Ubah Halaqoh' : 'Buat Halaqoh Baru' }}
    </h1>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ !empty($edit) ? route('tahfizh.halaqoh.pengampu.update', $current->id) : route('tahfizh.halaqoh.store') }}"
          class="space-y-6">
        @csrf
        @if(!empty($edit)) @method('PUT') @endif

        <!-- Baris 1: Unit, Guru, Keterangan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Unit -->
            <div>
                <label class="block mb-1 text-sm font-medium">Unit</label>
                <select name="unit_id" id="unit_id"
                        class="w-full border rounded-lg px-3 py-2 dark:bg-gray-900 dark:border-gray-700"
                        {{ empty($edit) ? 'required' : '' }}>
                    <option value="">— Pilih Unit —</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}"
                                @selected(old('unit_id', $current->unit_id ?? '') == $u->id)>
                            {{ $u->nama_unit }} (ID {{ $u->id }})
                        </option>
                    @endforeach
                </select>
                @if(auth()->user()->hasRole('koordinator_tahfizh_putra') || auth()->user()->hasRole('koordinator_tahfizh_putri'))
                    <p class="text-xs text-gray-500 mt-1">Koordinator terkunci pada unitnya sendiri.</p>
                @endif
            </div>

            <!-- Guru -->
            <div>
                <label class="block mb-1 text-sm font-medium">Guru Pengampu</label>
                <select name="guru_id" id="guru_id"
                        class="w-full border rounded-lg px-3 py-2 dark:bg-gray-900 dark:border-gray-700" required>
                    <option value="">— Pilih Guru —</option>
                    @if(!empty($edit) && !empty($gurus))
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" @selected(old('guru_id', $current->guru_id ?? '') == $g->id)>{{ $g->nama }}</option>
                        @endforeach
                    @endif
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Aturan: <b>1 guru hanya boleh punya 1 halaqoh</b>.
                </p>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block mb-1 text-sm font-medium">Keterangan (opsional)</label>
                <input type="text" name="keterangan" value="{{ old('keterangan', $current->keterangan ?? '') }}"
                       class="w-full border rounded-lg px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
            </div>
        </div>

        <!-- Santri -->
        <div>
            <div class="flex items-center justify-between">
                <label class="block mb-1 text-sm font-medium">Santri (otomatis sesuai unit & gender guru)</label>
                <span id="hintGender" class="text-xs text-gray-500"></span>
            </div>

            <select multiple name="santri[]" id="santri"
                    class="w-full border rounded-lg px-3 py-2 h-64 dark:bg-gray-900 dark:border-gray-700">
                @if(!empty($edit) && !empty($santriAll))
                    @foreach($santriAll as $s)
                        <option value="{{ $s->id }}" @selected(in_array($s->id, old('santri', $santriTerpilih ?? [])))>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                @endif
            </select>

            <p class="text-xs text-gray-500 mt-1">
                Daftar santri ditampilkan bila <b>unit telah dipilih</b> dan <b>guru telah dipilih</b>.
                Hanya santri <b>unit yang sama</b>, <b>gender sama</b> dengan guru, dan <b>belum tergabung halaqoh lain</b>.
            </p>
        </div>

        <!-- Aksi -->
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
                {{ !empty($edit) ? 'Simpan Perubahan' : 'Simpan Halaqoh' }}
            </button>
            <a href="{{ route('tahfizh.halaqoh.index') }}" class="px-4 py-2 rounded-lg border">
                Batal
            </a>
        </div>
    </form>
</div>

{{-- AJAX: muat guru & santri mengikuti unit + filter gender --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const unit = document.getElementById('unit_id');
    const guru = document.getElementById('guru_id');
    const santri = document.getElementById('santri');
    const hintGender = document.getElementById('hintGender');

    function clearOptions(selectEl, placeholder) {
        selectEl.innerHTML = '';
        if (placeholder !== null) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = placeholder || '—';
            selectEl.appendChild(opt);
        }
    }

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) return [];
        return await res.json();
    }

    async function loadGuru(unitId) {
        clearOptions(guru, '— Pilih Guru —');
        hintGender.textContent = '';
        if (!unitId) return;
        const data = await fetchJson(`/tahfizh/ajax/guru-by-unit/${unitId}`);
        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nama;
            opt.dataset.gender = item.jenis_kelamin || '';
            guru.appendChild(opt);
        });
    }

    async function loadSantri(unitId, guruId) {
        santri.innerHTML = '';
        if (!unitId || !guruId) return;
        const data = await fetchJson(`/tahfizh/ajax/santri-by-unit/${unitId}?guru_id=${guruId}`);
        data.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nama;
            santri.appendChild(opt);
        });
    }

    function updateGenderHint() {
        const option = guru.options[guru.selectedIndex];
        const g = option?.dataset?.gender || '';
        hintGender.textContent = g ? `Gender guru: ${g === 'L' ? 'Laki-laki' : 'Perempuan'}` : '';
    }

    unit?.addEventListener('change', async (e) => {
        const unitId = e.target.value;
        await loadGuru(unitId);
        updateGenderHint();
        await loadSantri(unitId, guru.value || '');
    });

    guru?.addEventListener('change', async (e) => {
        updateGenderHint();
        const unitId = unit.value;
        const guruId = e.target.value;
        await loadSantri(unitId, guruId);
    });
});
</script>
@endsection
