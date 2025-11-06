{{-- ==============================
📘 Tahfizh Dashboard (Versi Terbaru)
Tujuan: Menampilkan data real hafalan per halaqoh menggunakan Chart.js
File: resources/views/tahfizh/dashboard.blade.php
============================== --}}

@extends('layouts.admin')
@section('content')
<x-breadcrumb />

{{-- Statistik Utama --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="p-4 bg-indigo-100 dark:bg-indigo-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Halaqoh</h3>
        <p class="text-3xl font-bold">{{ $totalHalaqoh }}</p>
    </div>
    <div class="p-4 bg-teal-100 dark:bg-teal-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Santri</h3>
        <p class="text-3xl font-bold">{{ $totalSantri }}</p>
    </div>
    <div class="p-4 bg-yellow-100 dark:bg-yellow-900 rounded-2xl shadow">
        <h3 class="text-lg font-semibold">Total Hafalan</h3>
        <p class="text-3xl font-bold">{{ $totalHafalan }}</p>
    </div>
</div>

{{-- Grafik Hafalan per Halaqoh --}}
<div class="mt-10 bg-white dark:bg-gray-900 rounded-2xl shadow p-6">
    <h2 class="text-2xl font-semibold mb-4">📚 Hafalan per Halaqoh</h2>
    <canvas id="halaqohChart" height="100"></canvas>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxT = document.getElementById('halaqohChart').getContext('2d');
    const halaqohChart = new Chart(ctxT, {
        type: 'bar',
        data: {
            labels: {!! json_encode($hafalanPerHalaqoh->keys()) !!},
            datasets: [{
                label: 'Jumlah Hafalan per Halaqoh',
                data: {!! json_encode($hafalanPerHalaqoh->values()) !!},
                backgroundColor: 'rgba(147, 51, 234, 0.6)',
                borderColor: 'rgba(147, 51, 234, 1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' },
                title: {
                    display: true,
                    text: 'Rekap Hafalan Setiap Halaqoh',
                    font: { size: 16 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endsection

{{-- ==============================
📋 Penjelasan
- Statistik atas menampilkan total halaqoh, santri, dan hafalan.
- Grafik bar menampilkan jumlah hafalan per halaqoh secara real-time dari tabel hafalan_quran.
- Menggunakan warna ungu (Tailwind indigo/purple) agar berbeda dari dashboard guru.
- Data otomatis menyesuaikan isi database tanpa perlu ubah kode.
============================== --}}