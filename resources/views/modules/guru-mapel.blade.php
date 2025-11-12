@extends('layouts.app')
@section('label', 'Modul Guru Mapel')

@section('content')
<x-breadcrumb label="Modul Guru Mapel" />
@php
    $cards = [
        [
            'title' => 'Nilai Mapel',
            'description' => 'Input nilai mapel umum maupun syar’i.',
            'url' => route('guru.dashboard'),
            'icon' => '📘',
        ],
        [
            'title' => 'Absensi Mapel',
            'description' => 'Catat kehadiran santri setiap pertemuan.',
            'url' => route('filament.admin.resources.absensi-mapel.index'),
            'icon' => '📋',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
