<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en"
      x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="if (darkMode) document.documentElement.classList.add('dark')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-darkBg dark:text-darkText transition-colors duration-300 overflow-x-hidden">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 transform bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 transition-transform duration-300 ease-in-out md:translate-x-0"
        :class="{ '-translate-x-full': !sidebarOpen }">
        @include('components.sidebar')
    </aside>

    <!-- Overlay for Mobile -->
    <div class="fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"
         x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64 transition-all duration-300">
        <!-- Navbar -->
        <header class="fixed top-0 left-0 right-0 md:left-64 z-30 bg-white dark:bg-gray-900 dark:text-gray-100 shadow px-6 py-3 flex justify-between items-center">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="md:hidden text-gray-700 dark:text-gray-300 text-2xl focus:outline-none">☰</button>
            <h1 class="text-lg md:text-xl font-semibold">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center space-x-4">
                <button
                    @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode)"
                    class="text-xl focus:outline-none transition"
                    :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                    <template x-if="darkMode">☀️</template>
                    <template x-if="!darkMode">🌙</template>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-red-600 transition">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6 mt-16 bg-gray-50 dark:bg-gray-950">
            @yield('content')
        </main>
    </div>
</div>

{{-- ⬇️ penting: agar @push('scripts') di setiap halaman bisa tampil --}}
@stack('scripts')
</body>
</html>
