<!-- ==============================
📘 Navbar (resources/views/components/navbar.blade.php)
============================== -->
<header 
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="if (darkMode) document.documentElement.classList.add('dark')"
    @toggle-dark.window="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode);"
    class="bg-white dark:bg-gray-900 dark:text-gray-100 shadow px-6 py-3 flex justify-between items-center fixed top-0 left-0 right-0 z-30 transition-colors duration-300"
>
    <!-- Tombol Sidebar (Mobile) -->
    <button @click="$dispatch('toggle-sidebar')" class="md:hidden text-gray-700 dark:text-gray-300 text-2xl focus:outline-none">☰</button>

    <!-- Judul Halaman -->
    <h1 class="text-lg md:text-xl font-semibold">@yield('title', 'Dashboard')</h1>

    <!-- Info User + Logout -->
    <div class="flex items-center space-x-4">
        <!-- Dark Mode -->
        <button @click="$dispatch('toggle-dark')" class="text-xl focus:outline-none transition" :title="darkMode ? 'Light Mode' : 'Dark Mode'">
            <template x-if="darkMode">☀️</template>
            <template x-if="!darkMode">🌙</template>
        </button>

        <!-- Nama & Role -->
        <div class="text-sm leading-tight text-right">
            <div class="font-medium text-gray-800 dark:text-gray-200">{{ Auth::user()->name ?? 'User' }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                @php
                    $path = request()->path();
                    if (str_contains($path, 'guru')) echo 'Halaman Guru';
                    elseif (str_contains($path, 'admin')) echo 'Halaman Admin';
                    elseif (str_contains($path, 'wali')) echo 'Halaman Wali Santri';
                    elseif (str_contains($path, 'pimpinan')) echo 'Halaman Pimpinan';
                    elseif (str_contains($path, 'tahfizh')) echo 'Halaman Tahfizh';
                    else echo ucfirst(Auth::user()->role ?? 'Guest');
                @endphp
            </div>
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-red-600 transition">Logout</button>
        </form>
    </div>
</header>
