@extends('layouts.app')
@section('label', 'Modul Wakamad Sarpras')

@section('content')
<x-breadcrumb label="Modul Wakamad Sarpras" />
@php
    $cards = [
        [
            'title' => 'Inventaris Sarpras',
            'description' => 'Catat dan pantau kebutuhan sarana prasarana.',
            'url' => '#',
            'icon' => '',
        ],
        [
            'title' => 'Koordinasi Logistics',
            'description' => 'Arahkan kebutuhan peralatan melalui admin unit.',
            'url' => route('admin.unit.index'),
            'icon' => '',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
