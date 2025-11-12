@extends('layouts.app')
@section('label', 'Modul Mudir Pondok')

@section('content')
<x-breadcrumb label="Modul Mudir Pondok" />
@php
    $cards = [
        [
            'title' => 'Dashboard Pondok/MTS/MA',
            'description' => 'Lihat statistik gabungan unit pondok, MTS, dan MA.',
            'url' => route('pimpinan.dashboard'),
            'icon' => '🏫',
        ],
        [
            'title' => 'Koordinasi Kesantrian',
            'description' => 'Bimbing kabag kesantrian dan modul kesantrian.',
            'url' => route('module.kesantrian.putra'),
            'icon' => '🕌',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
