@extends('layouts.app')
@section('label', 'Modul Kabag Kesantrian Putri')

@section('content')
<x-breadcrumb label="Modul Kabag Kesantrian Putri" />
@php
    $cards = [
        [
            'title' => 'Koordinasi Tahfizh Putri',
            'description' => 'Atur halaqoh tahfizh, guru pengampu, dan progres setoran santri putri.',
            'url' => route('module.kesantrian.tahfizh', ['segment' => 'putri']),
            'icon' => '📖',
        ],
        [
            'title' => 'Koordinasi Lughoh Putri',
            'description' => 'Sinkronkan jadwal pembinaan lughoh dan evaluasi bahasa santri putri.',
            'url' => route('tahfizh.halaqoh.index'),
            'icon' => '📚',
        ],
        [
            'title' => 'Koordinasi Kesehatan Putri',
            'description' => 'Pantau laporan kesehatan, kontrol rutin, dan tindak lanjut santri putri.',
            'url' => route('module.koor-kesehatan'),
            'icon' => '🩺',
        ],
        [
            'title' => 'Koordinasi Kebersihan Putri',
            'description' => 'Atur jadwal piket dan inspeksi kebersihan asrama serta kelas putri.',
            'url' => route('module.koor-kebersihan'),
            'icon' => '🧹',
        ],
        [
            'title' => 'Koordinasi Keamanan Putri',
            'description' => 'Kelola jadwal keamanan, laporan insiden, dan kesiapsiagaan santri putri.',
            'url' => route('module.koor-keamanan'),
            'icon' => '🛡️',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
