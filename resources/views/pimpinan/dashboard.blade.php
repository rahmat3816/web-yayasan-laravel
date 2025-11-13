{{-- ==============================
 Tahap 10.3 - View Dashboard Dinamis
Tujuan: Menampilkan data nyata dari database dalam tampilan rapi per role
Folder: resources/views/
============================== --}}

{{-- ==============================
PIMPINAN DASHBOARD
resources/views/pimpinan/dashboard.blade.php
============================== --}}
@extends('layouts.admin')
@section('content')
<x-breadcrumb />

@php
    $cards = [
        [
            'title' => 'Ringkasan Yayasan',
            'description' => 'Statistik santri, guru, dan unit.',
            'url' => route('pimpinan.dashboard'),
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Admin',
            'description' => 'Kirim permintaan laporan atau pembaruan data.',
            'url' => 'mailto:admin@yayasan.local',
            'icon' => '',
        ],
    ];
@endphp

@include('dashboard.partials.action-cards', ['cards' => $cards])

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Santri</h3>
        <p class="text-3xl font-bold">{{ $totalSantri }}</p>
    </div>
    <div class="p-4 bg-green-100 dark:bg-green-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Guru</h3>
        <p class="text-3xl font-bold">{{ $totalGuru }}</p>
    </div>
    <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Unit</h3>
        <p class="text-3xl font-bold">{{ $totalUnit }}</p>
    </div>
</div>
@endsection
