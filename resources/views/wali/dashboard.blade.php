{{-- ==============================
📘 Wali Santri Dashboard (Versi Final)
Tujuan: Menampilkan progres hafalan anak dari DB
============================== --}}
@extends('layouts.wali')

@section('content')
    <x-breadcrumb />

    <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow text-center">
        <h2 class="text-2xl font-semibold mb-2">Assalamu'alaikum, {{ auth()->user()->name }}</h2>
        <p class="mb-4">Berikut progres hafalan anak Anda:</p>

        @if ($santri)
            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-2xl inline-block">
                <h3 class="text-lg font-semibold">{{ $santri->nama }}</h3>
                <p class="text-3xl font-bold">{{ $totalHafalan }} Hafalan</p>
            </div>
        @else
            <p class="text-gray-500">Data santri belum terhubung dengan akun ini.</p>
        @endif
    </div>
@endsection
