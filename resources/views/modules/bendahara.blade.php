@extends('layouts.app')
@section('label', 'Modul Bendahara')

@section('content')
<x-breadcrumb label="Modul Bendahara" />
@php
    $cards = [
        [
            'title' => 'Input Laporan Keuangan',
            'description' => 'Catat pemasukan & pengeluaran unit.',
            'url' => route('admin.laporan.index'),
            'icon' => '💰',
        ],
        [
            'title' => 'Laporan Yayasan',
            'description' => 'Koordinasi laporan bulanan dengan pimpinan.',
            'url' => route('pimpinan.dashboard'),
            'icon' => '📊',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
