@extends('layouts.app')
@section('label', 'Panel Tugas')

@section('content')
@php
    $user = auth()->user();
    $cards = [];

    $pushCard = function (string $title, string $description, string $url, string $icon) use (&$cards) {
        $cards[] = compact('title', 'description', 'url', 'icon');
    };

    if ($user->hasRole(['guru', 'wali_kelas'])) {
        $pushCard('Input Setoran Hafalan', 'Catat setoran terbaru untuk santri binaan Anda.', route('guru.setoran.index'), '');
        $pushCard('Rekap Hafalan', 'Lihat progres santri per halaqoh.', route('guru.setoran.rekap'), '');
    }

    if ($user->hasRole(['koordinator_tahfizh_putra', 'koordinator_tahfizh_putri']) || $user->hasJabatan(['koor_tahfizh_putra', 'koor_tahfizh_putri'])) {
        $pushCard('Kelola Halaqoh', 'Atur pengampu dan santri pada halaqoh tahfizh.', route('tahfizh.halaqoh.index'), '');
    }

    if ($user->hasRole(['wakamad_kurikulum', 'wakamad_kesiswaan', 'wakamad_sarpras'])) {
        $pushCard('Kalender Pendidikan', 'Susun agenda akademik unit Anda.', route('filament.admin.resources.kalender-pendidikan.index'), '');
    }

    if ($user->hasRole('bendahara')) {
        $pushCard('Input Laporan Keuangan', 'Lengkapi administrasi keuangan dan laporan rutin.', route('admin.laporan.index'), '');
    }

    if ($user->hasRole('wali_santri')) {
        $pushCard('Pantau Hafalan Anak', 'Lihat progres hafalan dan catatan kesehatan.', route('wali.progres'), '');
        $pushCard('Perbarui Profil Wali', 'Perbarui data kontak wali & santri.', route('wali.profil'), '');
    }

    if ($user->hasRole(['pimpinan', 'mudir_pondok', 'naibul_mudir', 'naibatul_mudir', 'kabag_kesantrian_putra', 'kabag_kesantrian_putri', 'kabag_umum'])) {
        $pushCard('Dashboard Pimpinan', 'Akses ringkasan unit dan pondok.', route('pimpinan.dashboard'), '');
    }

    if ($user->hasRole(['superadmin', 'admin', 'admin_unit']) || $user->hasJabatan(['admin_unit'])) {
        $pushCard('Masuk Control Panel (Filament)', 'Kelola data guru, santri, dan jabatan di control panel.', url('/filament'), '');
    }

    $panelCatalog = config('jabatan.panels', []);
    $panelSections = [];

    if ($user) {
        $canViewAll = $user->hasRole('superadmin');
        $resolveUrl = function (array $entry) {
            $route = $entry['route'] ?? null;
            if (is_array($route)) {
                return route($route['name'], $route['params'] ?? []);
            }
            if (is_string($route) && $route !== '') {
                return route($route);
            }
            return '#';
        };

        foreach ($panelCatalog as $category) {
            $items = [];

            foreach ($category['entries'] as $entry) {
                $roles = $entry['roles'] ?? [];
                $hasAccess = $canViewAll;

                if (!$hasAccess && !empty($roles)) {
                    $hasAccess = $user->hasRole($roles) || $user->hasJabatan($roles);
                }

                if ($hasAccess) {
                    $items[] = [
                        'title' => $entry['title'],
                        'description' => $entry['description'] ?? '',
                        'url' => $resolveUrl($entry),
                    ];
                }
            }

            if (!empty($items)) {
                $panelSections[] = [
                    'label' => $category['label'],
                    'items' => $items,
                ];
            }
        }
    }
@endphp

{{-- Hero --}}
<section class="hero-gradient text-white">
    <div class="relative z-10 grid gap-8 md:grid-cols-2">
        <div class="space-y-4">
            <span class="stat-badge">Hyper-vision Panel</span>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight font-display">Selamat datang kembali, {{ $user->name }} </h1>
            <p class="text-sm md:text-base text-white/80 max-w-xl">
                Pantau progres unit pendidikan, kelola tugas lintas jabatan, dan kolaborasikan laporan real-time langsung dari satu command center.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ url('/filament') }}"
                   class="btn btn-primary btn-sm rounded-full shadow-lg shadow-blue-500/30">
                    Buka Control Panel
                </a>
                <a href="{{ route('guru.dashboard') }}"
                   class="btn btn-outline btn-sm rounded-full text-white border-white/60 hover:bg-white/10">
                    Lihat Dashboard Guru
                </a>
            </div>
        </div>
        <div class="glass-card bg-white/20 dark:bg-slate-900/50 border border-white/30 dark:border-slate-800/60 p-6 shadow-2xl">
            <p class="text-sm uppercase tracking-[0.3em] text-white/70">Snapshot</p>
            <p class="text-4xl font-semibold mt-2">{{ now()->translatedFormat('l, d F Y') }}</p>
            <div class="mt-6 grid grid-cols-2 gap-4 text-white">
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Unit Aktif</p>
                    <p class="text-3xl font-semibold">{{ number_format($stats['totalUnits'] ?? ($panelSections ? count($panelSections) : 0)) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Tugas Prioritas</p>
                    <p class="text-3xl font-semibold">{{ count($cards) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="mt-8">
    <livewire:dashboard-stats :metrics="$stats ?? []" />
</section>

{{-- Action cards --}}
@include('dashboard.partials.action-cards', ['cards' => $cards])

{{-- Chart placeholder --}}
<section class="chart-placeholder">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Insights</p>
            <h2 class="text-xl font-semibold text-slate-800 dark:text-white">Tren Laporan & Hafalan</h2>
        </div>
        <button class="btn btn-sm btn-outline btn-primary rounded-full">Lihat Detail</button>
    </div>
    <div class="h-64 bg-white/50 dark:bg-slate-900/30 rounded-3xl border border-dashed border-white/40 dark:border-slate-700 flex items-center justify-center">
        <div class="text-center text-slate-400 dark:text-slate-500">
            <p class="text-lg font-semibold">Chart interaktif segera hadir</p>
            <p class="text-sm">Integrasi ke dataset Livewire + ApexCharts tersedia pada sprint berikutnya.</p>
        </div>
    </div>
</section>

{{-- Panel modules --}}
@if (!empty($panelSections))
    <section class="mt-10 space-y-6">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-gray-100">Modul Jabatan Anda</h2>
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($panelSections as $section)
                <div class="glass-card p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ $section['label'] }}</p>
                        <span class="badge badge-outline badge-sm">{{ count($section['items']) }} modul</span>
                    </div>
                    <div class="space-y-3">
                        @foreach ($section['items'] as $panel)
                            <a href="{{ $panel['url'] }}"
                               class="block glass-border rounded-2xl px-4 py-3 hover:bg-white/60 dark:hover:bg-slate-800/60 transition">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $panel['title'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $panel['description'] }}</p>
                                <span class="text-xs text-primary mt-2 inline-flex items-center gap-1">Buka modul ->
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
