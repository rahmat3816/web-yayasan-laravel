@extends('layouts.admin')
@section('title', ' Laporan Data Yayasan')

@section('content')
<x-breadcrumb title=" Laporan Data Yayasan" />
<x-admin.alert />

<x-admin.card title=" Statistik Data Yayasan">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <x-admin.stat label="Total Santri" :value="$totalSantri" color="indigo" icon="" />
        <x-admin.stat label="Total Guru" :value="$totalGuru" color="teal" icon="" />
        <x-admin.stat label="Total Unit" :value="$totalUnit" color="amber" icon="" />
        <x-admin.stat label="Total Halaqoh" :value="$totalHalaqoh" color="pink" icon="" />
    </div>
</x-admin.card>

<x-admin.card title=" Jumlah Santri per Unit">
    <canvas id="santriChart" height="100"></canvas>
</x-admin.card>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = {!! json_encode($santriPerUnit->pluck('nama_unit')) !!};
const data = {!! json_encode($santriPerUnit->pluck('total')) !!};

new Chart(document.getElementById('santriChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Jumlah Santri',
            data,
            backgroundColor: 'rgba(59,130,246,0.6)',
            borderColor: 'rgba(59,130,246,1)',
            borderWidth: 1
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection
