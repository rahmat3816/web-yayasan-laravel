{{-- ===========================================================
 Komponen Chart Card (Admin)
Untuk menampilkan Chart.js dalam style konsisten.
=========================================================== --}}
@props(['label', 'id' => 'chart'])

<div class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6 mb-8">
    <h3 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200">
        {!! $label !!}
    </h3>
    <canvas id="{{ $id }}" height="120"></canvas>
</div>
