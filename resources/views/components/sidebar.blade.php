{{-- ==============================
📗 Sidebar - Optimized Version
============================== --}}
@php
    $is = fn(string $pattern) => request()->is($pattern);
    $linkBase = 'block px-3 py-2 rounded-lg transition';
    $active   = 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300';
    $hover    = 'hover:bg-blue-50 dark:hover:bg-gray-700';
@endphp

<div class="h-full flex flex-col">
    {{-- Header --}}
    <div class="p-4 font-bold text-lg text-blue-600 dark:text-blue-400 border-b dark:border-gray-700 text-center">
        Yayasan As-Sunnah
    </div>

    {{-- Navigasi --}}
    <nav class="mt-2 flex-1 overflow-y-auto">
        <ul class="px-2 py-2 space-y-1 text-[15px]">

            {{-- ========== DASHBOARD UTAMA ========== --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="{{ $linkBase }} {{ $is('dashboard') ? $active : $hover }}">
                    🏠 Dashboard Utama
                </a>
            </li>

            {{-- Profil Umum --}}
            <li>
                <a href="{{ url('/profile') }}"
                   class="{{ $linkBase }} {{ $is('profile') ? $active : $hover }}">
                    👤 Profil Pengguna
                </a>
            </li>

            {{-- ========== ADMIN / OPERATOR / SUPERADMIN ========== --}}
            @role(['superadmin','admin','operator'])
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Data & Administrasi</li>

                {{-- Data Master --}}
                <li>
                    <a href="{{ route('admin.santri.index') }}"
                       class="{{ $linkBase }} {{ $is('admin/santri*') ? $active : $hover }}">
                        👨‍🎓 Data Santri
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.guru.index') }}"
                       class="{{ $linkBase }} {{ $is('admin/guru*') ? $active : $hover }}">
                        👩‍🏫 Data Guru
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.halaqoh.index') }}"
                       class="{{ $linkBase }} {{ $is('admin/halaqoh*') ? $active : $hover }}">
                        📚 Data Halaqoh
                    </a>
                </li>

                @role('superadmin')
                    <li>
                        <a href="{{ route('admin.unit.index') }}"
                           class="{{ $linkBase }} {{ $is('admin/unit*') ? $active : $hover }}">
                            🏫 Unit Pendidikan
                        </a>
                    </li>
                @endrole

                {{-- Dropdown: Laporan --}}
                <li x-data="{ open: {{ $is('admin/laporan*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2 rounded-lg transition hover:bg-blue-50 dark:hover:bg-gray-700">
                        <span class="flex items-center">
                            📑 <span class="ml-2 font-semibold">Laporan</span>
                        </span>
                        <svg :class="open ? 'rotate-180 text-blue-600' : 'text-gray-500'"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <ul x-show="open" x-collapse class="pl-7 mt-1 space-y-1 text-[14px]">
                        <li>
                            <a href="{{ route('admin.laporan.index') }}"
                               class="{{ $linkBase }} {{ $is('admin/laporan') ? $active : $hover }}">
                                📊 Laporan Data Yayasan
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.laporan.hafalan') }}"
                               class="{{ $linkBase }} {{ $is('admin/laporan/hafalan*') ? $active : $hover }}">
                                📖 Laporan Hafalan Qur’an
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.laporan.hafalan') }}?mode=statistik"
                               class="{{ $linkBase }} {{ $is('admin/laporan/hafalan/statistik*') ? $active : $hover }}">
                                📈 Statistik Hafalan Santri
                            </a>
                        </li>
                    </ul>
                </li>
            @endrole

            {{-- ========== GURU & KOORDINATOR TAHFIZH ========== --}}
            @role(['guru','koordinator_tahfizh_putra','koordinator_tahfizh_putri','superadmin'])
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Halaqoh & Hafalan</li>

                <li>
                    <a href="{{ route('guru.dashboard') }}"
                       class="{{ $linkBase }} {{ $is('guru/dashboard') ? $active : $hover }}">
                        📘 Dashboard Guru
                    </a>
                </li>

                <li>
                    <a href="{{ route('guru.setoran.index') }}"
                       class="{{ $linkBase }} {{ $is('guru/setoran*') ? $active : $hover }}">
                        🕌 Setoran Hafalan
                    </a>
                </li>
            @endrole

            {{-- ========== KOORDINATOR / ADMIN / SUPERADMIN ========== --}}
            @role(['koordinator_tahfizh_putra','koordinator_tahfizh_putri','admin','superadmin'])
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Koordinasi Tahfizh</li>

                <li>
                    <a href="{{ route('tahfizh.halaqoh.index') }}"
                       class="{{ $linkBase }} {{ $is('tahfizh/halaqoh*') ? $active : $hover }}">
                        🧩 Kelola Halaqoh
                    </a>
                </li>
            @endrole

            {{-- ========== WALI SANTRI ========== --}}
            @role('wali_santri')
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Wali Santri</li>

                <li>
                    <a href="{{ route('wali.dashboard') }}"
                       class="{{ $linkBase }} {{ $is('wali/dashboard') ? $active : $hover }}">
                        👨‍👩‍👧 Data Anak
                    </a>
                </li>
                <li>
                    <a href="{{ route('wali.hafalan') }}"
                       class="{{ $linkBase }} {{ $is('wali/hafalan') ? $active : $hover }}">
                        📖 Progres Hafalan
                    </a>
                </li>
            @endrole
        </ul>
    </nav>
</div>

{{-- AlpineJS untuk dropdown --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
