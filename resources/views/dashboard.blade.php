@extends('layouts.app')

@section('title', 'Panel Pengguna')

@section('content')
    <x-breadcrumb label="Panel Pengguna" />

    @php
        $cards = [
            [
                'title' => 'Masuk Control Panel',
                'description' => 'Kelola data melalui Filament.',
                'url' => url('/filament'),
                'icon' => '',
            ],
            [
                'title' => 'Pantau Hafalan',
                'description' => 'Arahkan ke modul setoran hafalan.',
                'url' => route('guru.setoran.index'),
                'icon' => '',
            ],
        ];
    @endphp

    @include('dashboard.partials.action-cards', ['cards' => $cards])

    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 text-gray-600 dark:text-gray-300 shadow">
        <p class="text-lg font-semibold">Selamat datang di portal SIYASGO.</p>
        <p class="mt-2 text-sm">Anda akan diarahkan otomatis sesuai peran, namun Anda juga bisa menggunakan tombol di atas untuk memulai tugas.</p>
    </div>
@endsection
