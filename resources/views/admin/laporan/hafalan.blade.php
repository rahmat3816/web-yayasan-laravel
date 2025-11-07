{{-- ==============================
📊 Laporan Hafalan Qur’an (Admin)
Menggunakan komponen x-admin.*
============================== --}}
@extends('layouts.admin')

@section('title', '📖 Laporan Hafalan Qur’an')

@section('content')
<x-breadcrumb title="📖 Laporan Hafalan Qur’an" />
<x-admin.alert />

{{-- 🎛️ Filter --}}
<x-admin.filter-bar
    :action="route('admin.laporan.hafalan')"
    :resetRoute="route('admin.laporan.hafalan')"
>
    <x-slot name="fields">
        <div>
            <label class="text-sm font-semibold">Tahun</label>
            <select name="tahun" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                @foreach (range(now()->year - 4, now()->year) as $t)
                    <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">Bulan</label>
            <select name="bulan" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                @foreach (range(1, 12) as $b)
                    <option value="{{ $b }}" {{ $b == $bulan ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">Unit</label>
            <select name="unit_id" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                <option value="">-- Semua Unit --</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>
                        {{ $u->nama_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">Guru</label>
            <select name="guru_id" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                <option value="">-- Semua Guru --</option>
                @foreach ($guruList as $g)
                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                        {{ $g->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold">Santri</label>
            <select name="santri_id" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                <option value="">-- Semua Santri --</option>
                @foreach ($santriList as $s)
                    <option value="{{ $s->id }}" {{ $santriId == $s->id ? 'selected' : '' }}>
                        {{ $s->nama }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-slot>
</x-admin.filter-bar>

{{-- 📊 Statistik Ringkas --}}
<x-admin.card title="📊 Statistik Hafalan Qur’an">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-center">
        <x-admin.stat label="Total Guru" :value="$rekap['total_guru'] ?? 0" color="amber" icon="👩‍🏫" />
        <x-admin.stat label="Total Santri" :value="$rekap['total_santri'] ?? 0" color="teal" icon="👨‍🎓" />
        <x-admin.stat label="Total Setoran" :value="$rekap['total_setoran'] ?? 0" color="indigo" icon="🕌" />
        <x-admin.stat label="Total Juz" :value="$rekap['total_juz'] ?? 0" color="green" icon="📘" />
        <x-admin.stat label="Total Surah" :value="$rekap['total_surah'] ?? 0" color="blue" icon="🕋" />
        <x-admin.stat label="Total Halaman" :value="$rekap['total_halaman'] ?? 0" color="pink" icon="📄" />
    </div>
</x-admin.card>

{{-- 📈 Grafik Harian --}}
<x-admin.chart-card title="📅 Capaian Setoran Harian" id="chartHarian" />

@if($rekapGuru->isNotEmpty())
    <x-admin.chart-card title="👨‍🏫 Rekap Hafalan per Guru" id="chartGuru" />
@endif

@if($data->isNotEmpty())
    @php $rekapJuz = $data->groupBy('juz_start')->map->count(); @endphp
    <x-admin.chart-card title="📘 Distribusi Setoran per Juz" id="chartJuz" />
@endif

{{-- 📋 Tabel --}}
<x-admin.card title="📋 Data Hafalan Santri">
    <x-admin.table>
        <x-slot name="head">
            <tr>
                <th>Tanggal</th>
                <th>Santri</th>
                <th>Guru</th>
                <th>Surah</th>
                <th>Ayat</th>
                <th>Juz</th>
                <th>Catatan</th>
            </tr>
        </x-slot>

        <x-slot name="body">
            @forelse($data as $h)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($h->tanggal_setor)->translatedFormat('d M Y') }}</td>
                    <td>{{ $h->santri->nama ?? '-' }}</td>
                    <td>{{ $h->guru->nama ?? '-' }}</td>
                    <td>Surah {{ $h->surah_id }}</td>
                    <td class="text-center">{{ $h->ayah_start }}–{{ $h->ayah_end }}</td>
                    <td class="text-center">{{ $h->juz_start }}</td>
                    <td>{{ $h->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-3">Belum ada data hafalan.</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.table>
</x-admin.card>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = {!! json_encode($grafikPerHari->keys() ?? []) !!};
    const data = {!! json_encode($grafikPerHari->values() ?? []) !!};

    new Chart(document.getElementById('chartHarian'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Setoran per Hari',
                data: data,
                borderColor: 'rgb(59,130,246)',
                backgroundColor: 'rgba(59,130,246,0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush
@endsection
