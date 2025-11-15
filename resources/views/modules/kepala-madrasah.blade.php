@extends('layouts.app')
@section('label', 'Modul Kepala Madrasah')

@section('content')
<x-breadcrumb label="Modul Kepala Madrasah" />
@php
    $cards = [
        [
            'title' => 'Dashboard Unit Pendidikan',
            'description' => 'Pantau data santri, guru, dan kelas di unit Anda.',
            'url' => route('admin.dashboard'),
            'icon' => '',
        ],
        [
            'title' => 'Kelola Guru & Wali Kelas',
            'description' => 'Perbarui data guru dan penugasan wali kelas.',
            'url' => route('admin.guru.index'),
            'icon' => '',
        ],
        [
            'title' => 'Data Santri',
            'description' => 'Cek mutasi santri, absensi, dan progres hafalan.',
            'url' => route('admin.santri.index'),
            'icon' => '',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Panduan Singkat</h2>
    <ul class="list-disc ms-5 text-sm text-gray-600 dark:text-gray-300 space-y-1">
        <li>Gunakan tombol "Dashboard Unit" untuk melihat statistik cepat unit Anda.</li>
        <li>Penugasan jabatan guru dilakukan melalui menu "Guru & Jabatan" di Filament.</li>
        <li>Koordinasikan laporan bulanan dengan wakamad dan bendahara melalui menu laporan.</li>
    </ul>
</div>
@endsection
