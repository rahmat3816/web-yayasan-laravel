{{-- ==============================
📈 Statistik Hafalan Santri (Laravel Blade)
Terintegrasi dengan komponen <x-admin.stat>, <x-admin.chart-card>, <x-admin.table>
============================== --}}
@extends('layouts.admin')
@section('title', '📈 Statistik Hafalan Santri')

@section('content')
<x-breadcrumb title="📈 Statistik Hafalan Santri" />

<div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow">
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">🎓 Statistik Hafalan Santri</h2>

    {{-- 🔍 Filter --}}
    <x-admin.filter-bar
    :action="route('admin.laporan.hafalan')"
    :resetRoute="route('admin.laporan.hafalan', ['mode' => 'statistik'])"
    >
        <x-slot name="fields">
            <div>
                <label class="text-sm font-semibold">Unit</label>
                <select name="unit_id" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                    <option value="">-- Semua Unit --</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" {{ $u->id == $unitId ? 'selected' : '' }}>
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
                        <option value="{{ $g->id }}" {{ $g->id == $guruId ? 'selected' : '' }}>
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
                        <option value="{{ $s->id }}" {{ $s->id == $santriId ? 'selected' : '' }}>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold">Tahun</label>
                <select name="tahun" class="w-full border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-100">
                    @foreach (range(now()->year - 2, now()->year) as $t)
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
        </x-slot>
    </x-admin.filter-bar>

    {{-- 📊 Ringkasan Capaian --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <x-admin.stat label="Total Setoran" :value="$rekap['total_setoran'] ?? 0" color="indigo" icon="🕌" />
        <x-admin.stat label="Total Santri" :value="$rekap['total_santri'] ?? 0" color="teal" icon="👨‍🎓" />
        <x-admin.stat label="Total Guru" :value="$rekap['total_guru'] ?? 0" color="amber" icon="👩‍🏫" />
        <x-admin.stat label="Total Halaman" :value="$rekap['total_halaman'] ?? 0" color="pink" icon="📄" />
        <x-admin.stat label="Total Juz" :value="$rekap['total_juz'] ?? 0" color="green" icon="📘" />
        <x-admin.stat label="Total Surah" :value="$rekap['total_surah'] ?? 0" color="blue" icon="🕋" />
    </div>

    {{-- 📈 Grafik Progres Hafalan --}}
    <x-admin.chart-card title="📈 Progres Hafalan Santri" id="grafikHafalan" />

    {{-- 🧾 Tabel Data --}}
    <x-admin.table>
        <x-slot name="head">
            <tr>
                <th>#</th>
                <th>Santri</th>
                <th>Guru</th>
                <th>Juz</th>
                <th>Surah</th>
                <th>Ayat</th>
                <th>Tanggal</th>
            </tr>
        </x-slot>

        <x-slot name="body">
            @forelse ($data as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->santri->nama ?? '-' }}</td>
                    <td>{{ $row->guru->nama ?? '-' }}</td>
                    <td>{{ $row->juz_start ?? '-' }}</td>
                    <td>{{ $row->surah_id ?? '-' }}</td>
                    <td>{{ $row->ayah_start }} - {{ $row->ayah_end }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->tanggal_setor)->translatedFormat('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 dark:text-gray-400 py-3">
                        Tidak ada data hafalan ditemukan.
                    </td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.table>
</div>

{{-- 📊 Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const santriId = "{{ $santriId }}";
    if (!santriId) return;

    const url = "{{ route('admin.laporan.hafalan.grafikSantri', ['id' => '__ID__']) }}".replace('__ID__', santriId);

    try {
        const res = await fetch(url);
        const data = await res.json();

        const ctx = document.getElementById('grafikHafalan');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.tanggal),
                datasets: [{
                    label: 'Total Ayat Kumulatif',
                    data: data.map(d => d.total),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(34,197,94,0.2)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Jumlah Ayat' } },
                    x: { title: { display: true, text: 'Tanggal Setoran' } }
                },
                plugins: { legend: { display: true, position: 'top' } }
            }
        });
    } catch (e) {
        console.error('Gagal memuat data grafik:', e);
    }
});
</script>
@endsection
