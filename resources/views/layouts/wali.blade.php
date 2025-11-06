<!-- resources/views/layouts/wali.blade.php (versi final dan seragam) -->
<!DOCTYPE html>
<html lang="en"
      x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="if (darkMode) document.documentElement.classList.add('dark')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Wali Santri')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-darkBg dark:text-darkText transition-colors duration-300 overflow-x-hidden">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 transform bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 transition-transform duration-300 ease-in-out md:translate-x-0"
        :class="{ '-translate-x-full': !sidebarOpen }">
        @include('components.sidebar-wali')
    </aside>

    <!-- Overlay for mobile -->
    <div
        class="fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition.opacity>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64 transition-all duration-300">

        <!-- Navbar -->
        @include('components.navbar-wali')

        <!-- Page Content -->
        <main class="flex-1 p-6 mt-16 bg-gray-50 dark:bg-gray-950">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-green-700 dark:bg-green-800 text-white text-center p-3">
            <p class="text-sm">© {{ date('Y') }} Yayasan As-Sunnah Gorontalo</p>
        </footer>
    </div>
</div>
</body>
</html>
