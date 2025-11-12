@extends('layouts.app')
@section('label', 'Panel Koordinator Keamanan')

@section('content')
<div class="space-y-6">
    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Jadwal Piket & Laporan Insiden</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Distribusikan jadwal keamanan asrama, catat pelanggaran, dan tindak lanjutnya.
        </p>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300 space-y-2">
            <p>• Form jadwal piket digital akan tersedia setelah modul final.</p>
            <p>• Rekap insiden bisa dilampirkan ke admin hingga fitur input selesai.</p>
        </div>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-amber-400 dark:border-amber-300 p-6 text-center shadow">
        <p class="text-5xl mb-3">🛡️</p>
        <p class="text-lg font-semibold text-gray-800 dark:text-gray-100">Modul keamanan dalam pengembangan</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Segera hadir fitur log insiden, status penanganan, dan koordinasi dengan kabag kesantrian.
        </p>
    </section>
</div>
@endsection
