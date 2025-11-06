{{-- ==============================
📗 Sidebar (components/sidebar.blade.php)
Catatan:
- Tidak ada x-data di sini → state dikendalikan layout.
- Tidak ada JS preventDefault → perbaiki “klik dua kali”.
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

    {{-- Menu --}}
    <nav class="mt-2 flex-1 overflow-y-auto">
        <ul class="px-2 py-2 space-y-1 text-[15px]">

            {{-- Dashboard & Profil --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="{{ $linkBase }} {{ $is('dashboard') ? $active : $hover }}">
                    🏠 Dashboard
                </a>
            </li>
            <li>
                <a href="{{ url('/profile') }}"
                   class="{{ $linkBase }} {{ $is('profile') ? $active : $hover }}">
                    👤 Profil
                </a>
            </li>

            {{-- ========= Admin / Operator / Superadmin ========= --}}
            @role(['superadmin','admin','operator'])
                <li class="mt-3 px-3 text-xs uppercase tracking-wider text-gray-400">Administrasi</li>

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

                {{-- Unit Pendidikan: hanya SUPERADMIN --}}
                @role('superadmin')
                    <li>
                        <a href="{{ route('admin.unit.index') }}"
                           class="{{ $linkBase }} {{ $is('admin/unit*') ? $active : $hover }}">
                            🏫 Unit Pendidikan
                        </a>
                    </li>
                @endrole

                <li>
                    <a href="{{ route('admin.laporan.index') }}"
                       class="{{ $linkBase }} {{ $is('admin/laporan*') ? $active : $hover }}">
                        📊 Laporan
                    </a>
                </li>
            @endrole

            {{-- ========= Guru & Koordinator (juga superadmin untuk pantau) ========= --}}
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

            {{-- ========= Koordinator Tahfizh / Admin / Superadmin ========= --}}
            @role(['koordinator_tahfizh_putra','koordinator_tahfizh_putri','admin','superadmin'])
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Koordinasi Tahfizh</li>

                <li>
                    <a href="{{ route('tahfizh.halaqoh.index') }}"
                       class="{{ $linkBase }} {{ $is('tahfizh/halaqoh*') ? $active : $hover }}">
                        🧩 Kelola Halaqoh
                    </a>
                </li>
            @endrole

            {{-- ========= Wali Santri ========= --}}
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
