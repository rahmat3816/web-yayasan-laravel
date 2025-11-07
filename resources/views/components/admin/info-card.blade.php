{{-- ==============================
ℹ️ Komponen: Info Card / Highlight Box
Usage:
<x-admin.info-card title="Peringatan" message="Belum ada setoran bulan ini" color="yellow" />
============================== --}}
@props([
    'title' => 'Info',
    'message' => 'Keterangan belum diisi.',
    'color' => 'blue',
])

@php
    $colorClass = match($color) {
        'yellow' => 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'red'    => 'bg-red-100 border-l-4 border-red-500 text-red-800 dark:bg-red-900 dark:text-red-200',
        'green'  => 'bg-green-100 border-l-4 border-green-500 text-green-800 dark:bg-green-900 dark:text-green-200',
        'blue'   => 'bg-blue-100 border-l-4 border-blue-500 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        default  => 'bg-gray-100 border-l-4 border-gray-400 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
    };
@endphp

<div class="{{ $colorClass }} p-4 rounded-lg shadow-sm mb-4">
    <p class="font-semibold">{{ $title }}</p>
    <p class="text-sm mt-1">{{ $message }}</p>
</div>
