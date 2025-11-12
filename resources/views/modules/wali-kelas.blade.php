@extends('layouts.app')
@section('label', 'Modul Wali Kelas')

@section('content')
<x-breadcrumb label="Modul Wali Kelas" />
@php
    $cards = [
        [
            'title' => 'Kelola Nilai & Raport',
            'description' => 'Isi nilai harian, ujian, dan finalisasi raport.',
            'url' => route('guru.dashboard'),
            'icon' => '🧾',
        ],
        [
            'title' => 'Koordinasi dengan Wakamad',
            'description' => 'Kirim laporan nilai ke wakamad kurikulum.',
            'url' => route('module.wakamad.kurikulum'),
            'icon' => '📤',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
