@extends('layouts.admin')

@section('title', 'Laporan Data Yayasan')

@section('content')
    <x-breadcrumb title="📊 Laporan Data Yayasan" />

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
        <div class="p-4 bg-indigo-100 dark:bg-indigo-900 rounded-2xl shadow">
            <h3 class="text-lg font-semibold">Total Santri</h3>
            <p class="text-3xl font-bold">{{ $totalSantri }}</p>
        </div>
        <div class="p-4 bg-teal-100 dark:bg-teal-900 rounded-2xl shadow">
            <h3 class="text-lg font-semibold">Total Guru</h3>
            <p class="text-3xl font-bold">{{ $totalGuru }}</p>
        </div>
        <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-2xl shadow">
            <h3 class="text-lg font-semibold">Total Unit</h3>
            <p class="text-3xl font-bold">{{ $totalUnit }}</p>
        </div>
        <div class="p-4 bg-pink-100 dark:bg-pink-900 rounded-2xl shadow">
            <h3 class="text-lg font-semibold">Total Halaqoh</h3>
            <p class="text-3xl font-bold">{{ $totalHalaqoh }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow">
        <h2 class="text-xl font-semibold mb-4">📈 Jumlah Santri per Unit</h2>
        <canvas id="santriChart" height="100"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($santriPerUnit->pluck('nama_unit')) !!};
        const data = {!! json_encode($santriPerUnit->pluck('total')) !!};

        new Chart(document.getElementById('santriChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Santri',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
@endsection
