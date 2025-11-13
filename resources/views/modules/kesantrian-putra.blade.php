@extends('layouts.app')
@section('label', 'Modul Kabag Kesantrian Putra')

@section('content')
<x-breadcrumb label="Modul Kabag Kesantrian Putra" />
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
            'description' => 'Pantau laporan kesehatan, kontrol rutin, dan tindak lanjut santri putra.',
            'url' => route('module.koor-kesehatan'),
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
