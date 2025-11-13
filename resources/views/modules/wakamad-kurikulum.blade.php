@extends('layouts.app')
@section('label', 'Modul Wakamad Kurikulum')

@section('content')
<x-breadcrumb label="Modul Wakamad Kurikulum" />
@php
    $cards = [
        [
            'title' => 'Kalender Pendidikan',
            'description' => 'Susun kalender akademik dan agenda evaluasi.',
            'url' => route('filament.admin.resources.kalender-pendidikan.index'),
            'icon' => '',
        ],
        [
            'title' => 'Guru Mapel',
            'description' => 'Tetapkan guru mapel umum & syar'i per kelas.',
            'url' => route('filament.admin.resources.guru-mapel.index'),
            'icon' => '',
        ],
        [
            'title' => 'Monitoring Wali Kelas',
            'description' => 'Pastikan wali kelas sudah mengisi nilai & raport.',
            'url' => route('admin.guru.index'),
            'icon' => '',
        ],
    ];
@endphp
@include('dashboard.partials.action-cards', ['cards' => $cards])
@endsection
