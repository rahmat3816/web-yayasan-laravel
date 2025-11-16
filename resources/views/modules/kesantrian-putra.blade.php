@extends('layouts.app')
@section('label', 'Modul Kabag Kesantrian Putra')

@section('content')
<x-breadcrumb label="Modul Kabag Kesantrian Putra" />

<div class="grid gap-6">
    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Ringkasan Layanan Kesehatan Santri Putra</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Data real-time dari modul kesehatan Filament.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ url('/filament/kesantrian-dashboard') }}" class="px-4 py-2 rounded-full bg-amber-500 text-white text-sm font-semibold shadow hover:bg-amber-600">Dashboard Kesantrian</a>
                <a href="{{ url('/filament/santri-health-logs') }}" class="px-4 py-2 rounded-full border border-gray-300 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-200">Input / Monitoring</a>
            </div>
        </div>

        <div class="grid gap-4 mt-6 md:grid-cols-4">
            <x-stat-card label="Kasus Aktif" :value="$stats['active']" />
            <x-stat-card label="Dirujuk" :value="$stats['dirujuk']" />
            <x-stat-card label="Hari Ini" :value="$stats['today']" />
            <x-stat-card label="Total Laporan" :value="$stats['total']" />
        </div>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Riwayat Terbaru</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">5 catatan kesehatan terakhir.</p>
            </div>
            <a href="{{ url('/filament/kesehatan/rekap') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">Rekap Lengkap →</a>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="py-2">Tanggal</th>
                        <th class="py-2">Santri</th>
                        <th class="py-2">Asrama</th>
                        <th class="py-2">Keluhan</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="py-2">{{ optional($log->tanggal_sakit)->translatedFormat('d M') }}</td>
                            <td class="py-2 font-semibold text-gray-800 dark:text-gray-100">{{ $log->santri->nama ?? '-' }}</td>
                            <td class="py-2">{{ $log->asrama->nama ?? '-' }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($log->keluhan, 40) }}</td>
                            <td class="py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    @class([
                                        'bg-yellow-100 text-yellow-800' => $log->status === 'menunggu',
                                        'bg-blue-100 text-blue-800' => $log->status === 'ditangani',
                                        'bg-red-100 text-red-800' => $log->status === 'dirujuk',
                                        'bg-green-100 text-green-800' => $log->status === 'selesai',
                                    ])">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada catatan kesehatan terbaru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@php
    $cards = [
        [
            'title' => 'Koordinasi Tahfizh Putra',
            'description' => 'Atur halaqoh tahfizh, guru pengampu, dan progres setoran santri putra.',
            'url' => route('module.kesantrian.tahfizh', ['segment' => 'putra']),
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Lughoh Putra',
            'description' => 'Sinkronkan jadwal pembinaan lughoh dan evaluasi bahasa santri putra.',
            'url' => route('tahfizh.halaqoh.index'),
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Kesehatan Putra',
            'description' => 'Masuk ke modul kesehatan untuk input log dan tindakan.',
            'url' => url('/filament/santri-health-logs'),
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Kebersihan Putra',
            'description' => 'Atur jadwal piket dan inspeksi kebersihan asrama serta kelas putra.',
            'url' => route('module.koor-kebersihan'),
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Keamanan Putra',
            'description' => 'Kelola jadwal keamanan, laporan insiden, dan kesiapsiagaan santri putra.',
            'url' => route('module.koor-keamanan'),
            'icon' => '',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
