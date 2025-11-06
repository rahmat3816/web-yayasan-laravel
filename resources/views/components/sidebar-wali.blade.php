<!-- ==============================
📗 Sidebar (resources/views/components/sidebar.blade.php)
============================== -->
@php $path = request()->path(); @endphp
<div class="h-full flex flex-col">
    <!-- 🏫 Header -->
    <div class="p-4 font-bold text-xl text-blue-600 dark:text-blue-400 border-b text-center">
        Yayasan As-Sunnah
    </div>

    <!-- 📋 Menu Navigasi -->
    <nav class="mt-4 flex-1 overflow-y-auto">
        <ul class="space-y-2">

            {{-- Dashboard & Profil --}}
            <li>
                <a href="/dashboard"
                   class="block py-2 px-4 rounded transition
                   {{ str_starts_with($path, 'dashboard') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                      'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                    🏠 Dashboard
                </a>
            </li>
            <li>
                <a href="/profile"
                   class="block py-2 px-4 rounded transition
                   {{ str_starts_with($path, 'profile') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                      'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                    👤 Profil
                </a>
            </li>

            {{-- 🔒 Menu untuk Admin, Operator, Superadmin --}}
            @role(['superadmin','admin','operator'])
                <li>
                    <a href="/admin/santri"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'admin/santri') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        👨‍🎓 Data Santri
                    </a>
                </li>

                <li>
                    <a href="/admin/guru"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'admin/guru') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        👩‍🏫 Data Guru
                    </a>
                </li>

                <li>
                    <a href="/admin/halaqoh"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'admin/halaqoh') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        📚 Data Halaqoh
                    </a>
                </li>

                {{-- 🏫 Unit Pendidikan → hanya Superadmin --}}
                @if (Auth::user()->role === 'superadmin')
                    <li>
                        <a href="/admin/unit"
                           class="block py-2 px-4 rounded transition
                           {{ str_contains($path, 'admin/unit') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                              'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                            🏫 Unit Pendidikan
                        </a>
                    </li>
                @endif

                <li>
                    <a href="/admin/laporan"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'admin/laporan') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        📊 Laporan
                    </a>
                </li>
            @endrole

            {{-- 👩‍🏫 Menu untuk Guru & Koordinator & Superadmin (view-only setoran) --}}
            @role(['guru','wali_kelas','koordinator_tahfizh_putra','koordinator_tahfizh_putri','superadmin'])
                <li>
                    <a href="/guru/dashboard"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'guru/dashboard') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        📘 Dashboard Guru
                    </a>
                </li>

                <li>
                    <a href="/guru/setoran"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'guru/setoran') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        🧾 Setor Hafalan
                    </a>
                </li>

                <li>
                    <a href="/guru/laporan"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'guru/laporan') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        🧾 Laporan Harian
                    </a>
                </li>
            @endrole

            {{-- 👨‍👩‍👧 Menu untuk Wali Santri --}}
            @role('wali_santri')
                <li>
                    <a href="/wali/dashboard"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'wali/dashboard') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        👨‍👩‍👧 Data Anak
                    </a>
                </li>

                <li>
                    <a href="/wali/progres"
                       class="block py-2 px-4 rounded transition
                       {{ str_contains($path, 'wali/progres') ? 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300' :
                          'hover:bg-blue-100 dark:hover:bg-blue-900' }}">
                        📖 Progres Hafalan
                    </a>
                </li>
            @endrole

        </ul>
    </nav>
</div>
