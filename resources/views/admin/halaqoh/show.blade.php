@extends('layouts.admin')
@section('title', 'Detail Halaqoh')

@section('content')
<x-breadcrumb title="Detail Halaqoh" />

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4"> Detail Halaqoh</h1>

    <a href="{{ route('admin.halaqoh.index') }}" class="text-blue-600 hover:underline mb-4 block"><- Kembali ke Data Halaqoh</a>

    <div class="space-y-3 text-gray-700 dark:text-gray-200">
        <div><span class="font-semibold">Nama Halaqoh:</span> <p>{{ $halaqoh->nama_halaqoh }}</p></div>
        <div><span class="font-semibold">Guru Pembimbing:</span> <p>{{ $halaqoh->guru->nama ?? '-' }}</p></div>
        <div><span class="font-semibold">Unit:</span> <p>{{ $halaqoh->unit->nama_unit ?? '-' }}</p></div>
        <div><span class="font-semibold">Tanggal Dibuat:</span> <p>{{ $halaqoh->created_at->format('d M Y H:i') }}</p></div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('admin.halaqoh.edit', $halaqoh->id) }}"
           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition"> Edit Data</a>
    </div>
</div>
@endsection
