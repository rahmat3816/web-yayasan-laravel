<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Yayasan As-Sunnah') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-darkBg dark:text-darkText transition-colors duration-300">
<div x-data="{ sidebarOpen:false }" class="flex min-h-screen">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside
        class="fixed md:static z-30 h-screen md:h-auto w-72 md:w-64 shrink-0
               bg-white dark:bg-darkCard border-r border-gray-200 dark:border-gray-700
               transform md:transform-none transition-transform duration-300 ease-in-out
               -translate-x-full md:translate-x-0"
        :class="{ 'translate-x-0' : sidebarOpen }"
    >
        <x-sidebar />
    </aside>

    {{-- Overlay KHUSUS mobile --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen=false"
        class="fixed inset-0 bg-black/40 md:hidden z-20"
        style="display:none"
    ></div>

    {{-- ===================== KONTEN ===================== --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- TOPBAR --}}
        <nav class="sticky top-0 z-20 bg-white dark:bg-darkCard border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="h-14 px-4 md:px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    {{-- Hamburger hanya mobile --}}
                    <button type="button"
                            class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="sidebarOpen = !sidebarOpen"
                            aria-label="Buka menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="font-semibold">
                        {{ config('app.name', 'Yayasan As-Sunnah') }}
                    </a>
                </div>

                {{-- User menu sederhana --}}
                <div class="relative" x-data="{open:false}">
                    <button @click="open=!open" class="flex items-center gap-2 text-sm">
                        <span class="font-medium">{{ Auth::user()->name ?? 'Guest' }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition @click.outside="open=false"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-40"
                         style="display:none">
                        <div class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 border-b dark:border-gray-700">
                            <span class="font-semibold">Role:</span> {{ strtolower(Auth::user()->role ?? '-') }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Page content --}}
        <main class="flex-1 p-4 md:p-6 mt-2">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
