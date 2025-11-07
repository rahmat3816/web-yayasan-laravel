@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<x-breadcrumb title="📚 Dashboard Admin" />

<x-admin.card title="📊 Ringkasan Data Yayasan">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
        <x-admin.stat label="Santri" :value="$stats['totalSantri'] ?? 0" color="emerald" icon="👨‍🎓" />
        <x-admin.stat label="Guru" :value="$stats['totalGuru'] ?? 0" color="fuchsia" icon="👩‍🏫" />
        <x-admin.stat label="Halaqoh" :value="$stats['totalHalaqoh'] ?? 0" color="sky" icon="📖" />
        @if($isSuperadmin)
            <x-admin.stat label="Unit" :value="$stats['totalUnits'] ?? 0" color="amber" icon="🏫" />
            <x-admin.stat label="User" :value="$stats['totalUsers'] ?? 0" color="indigo" icon="👤" />
        @endif
    </div>
</x-admin.card>

@include('admin.partials.dashboard_extra')
@endsection
