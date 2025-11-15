@extends('layouts.app')
@section('label', 'Panel Koordinator Kesehatan')

@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Agenda Kesehatan Harian</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Catat laporan pemeriksaan, keluhan santri, dan tindak lanjut medis.
        </p>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
            <p> Form keluhan santri segera menyusul.</p>
            <p> Untuk sementara gunakan buku kontrol manual dan unggah saat modul siap.</p>
        </div>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-amber-400 dark:border-amber-300 p-6 text-center shadow">
        <p class="text-5xl mb-3"></p>
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Modul kesehatan sedang dikembangkan</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Tim IT menyiapkan pencatatan riwayat kesehatan, stok obat, dan jadwal kontrol berkala.
        </p>
    </section>
</div>
@endsection
