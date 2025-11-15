@extends('layouts.app')
@section('label', 'Modul Wakamad Kesiswaan')

@section('content')
<x-breadcrumb label="Modul Wakamad Kesiswaan" />
@php
    $cards = [
        [
            'title' => 'Data Santri & Absensi',
            'description' => 'Pantau aktivitas santri, absensi, dan catatan kedisiplinan.',
            'url' => route('admin.santri.index'),
            'icon' => '',
        ],
        [
            'title' => 'Laporan Kesiswaan',
            'description' => 'Susun laporan kegiatan ekstrakurikuler & pembinaan.',
            'url' => route('admin.laporan.index'),
            'icon' => '',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
