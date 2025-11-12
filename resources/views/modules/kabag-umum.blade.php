@extends('layouts.app')
@section('label', 'Modul Kabag Umum')

@section('content')
<x-breadcrumb label="Modul Kabag Umum" />
@php
    $cards = [
        [
            'title' => 'Kepegawaian',
            'description' => 'Koordinasi tugas staf pondok dan shift jaga.',
            'url' => '#',
            'icon' => '🧑‍💼',
        ],
        [
            'title' => 'Sarpras & Dapur',
            'description' => 'Pantau logistik dapur dan sarpras umum.',
            'url' => route('module.wakamad.sarpras'),
            'icon' => '🍽️',
        ],
        [
            'title' => 'Logistik',
            'description' => 'Catat permintaan barang dan persediaan gudang.',
            'url' => '#',
            'icon' => '📦',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
