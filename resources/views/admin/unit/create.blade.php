{{-- ==============================
 Tambah Unit - Admin & Operator
============================== --}}
@extends('layouts.admin')

@section('title', 'Tambah Unit')

@section('content')
    <x-breadcrumb title="Tambah Unit" />

    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-lg mt-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4">+ Tambah Data Unit</h1>

        <div class="mb-4">
            <a href="{{ route('admin.unit.index') }}" class="text-blue-600 hover:underline">
                <- Kembali ke Data Unit
            </a>
        </div>

        {{--  Pesan Error --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded mb-4 dark:bg-red-900 dark:text-red-100">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{--  Form Input --}}
        <form action="{{ route('admin.unit.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Unit</label>
                <input type="text" name="nama_unit" value="{{ old('nama_unit') }}"
                       class="w-full border dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 rounded px-3 py-2 focus:ring focus:ring-blue-300"
                       required>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">
                     Simpan Data
                </button>
            </div>
        </form>
    </div>
@endsection
