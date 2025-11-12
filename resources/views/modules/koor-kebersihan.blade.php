@extends('layouts.app')
@section('label', 'Panel Koordinator Kebersihan')

@section('content')
<div class="space-y-6">
    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Monitoring Kebersihan</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Rekap jadwal piket, checklist area bersih, dan laporan tindakan per unit.
        </p>
        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <li>• Jadwal piket digital akan tersedia di versi berikutnya.</li>
            <li>• Upload dokumentasi kebersihan melalui form Google sementara.</li>
        </ul>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-amber-400 dark:border-amber-300 p-6 text-center shadow">
        <p class="text-5xl mb-3">🧹</p>
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Modul kebersihan masih disiapkan</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Nantinya tersedia input checklist, inspeksi area, dan notifikasi kebersihan.
        </p>
    </section>
</div>
@endsection
