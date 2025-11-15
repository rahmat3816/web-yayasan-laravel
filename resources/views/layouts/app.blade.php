<!DOCTYPE html>
<html lang="id" x-data="appShell()" x-bind:data-theme="theme">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ trim($__env->yieldContent('label').' - ') }}{{ config('app.name', 'Yayasan As-Sunnah') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-transparent">
    <div class="drawer lg:drawer-open min-h-screen" x-cloak>
        <input id="app-drawer" type="checkbox" class="drawer-toggle" x-model="sidebarOpen">

        {{-- Main Content --}}
        <div class="drawer-content flex flex-col min-h-screen">
            <nav class="sticky top-0 z-20 mx-4 md:mx-6 mt-4 glass-card border border-white/40 dark:border-slate-800/70 shadow-glass">
                <div class="h-16 px-4 md:px-6 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <label for="app-drawer" class="btn btn-circle btn-ghost btn-sm lg:hidden" aria-label="Toggle sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </label>
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.35em] text-slate-400">Dashboard</p>
                            <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                                {{ config('app.name', 'Yayasan As-Sunnah') }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="btn btn-ghost btn-sm rounded-full" @click="toggleTheme()" aria-label="Toggle theme">
                            <svg x-show="theme === 'emerald'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
                            </svg>
                            <svg x-show="theme === 'emerald-dark'" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364-1.414 1.414M7.05 16.95l-1.414 1.414m0-11.314L7.05 7.05m9.9 9.9 1.414 1.414M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
                            </svg>
                        </button>

                        @php
                            $panelLinkAllowed = auth()->check() && (
                                in_array(strtolower(auth()->user()->role ?? ''), ['superadmin','admin']) ||
                                auth()->user()->hasRole(['superadmin','admin','admin_unit','kepala_madrasah']) ||
                                auth()->user()->hasJabatan(['admin_unit','kepala_madrasah'])
                            );
                        @endphp
                        @if ($panelLinkAllowed)
                            <a href="{{ url('/filament') }}" class="btn btn-sm btn-outline btn-primary hidden md:inline-flex">Panel Filament</a>
                        @endif

                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm rounded-full px-3">
                                <div class="flex items-center gap-2">
                                    <span class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-full w-9 h-9 text-sm font-semibold flex items-center justify-center">
                                            {{ strtoupper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                    </span>
                                    <div class="hidden sm:block text-left">
                                        <p class="text-sm font-semibold leading-tight">{{ auth()->user()->name ?? 'Guest' }}</p>
                                        <p class="text-xs text-slate-500 capitalize">{{ strtolower(auth()->user()->role ?? '-') }}</p>
                                    </div>
                                </div>
                            </label>
                            <div tabindex="0" class="dropdown-content mt-3 w-64 card bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-white/60 dark:border-slate-700 shadow-xl p-4 space-y-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ auth()->user()->name ?? 'Guest' }}</p>
                                    <p class="text-xs text-slate-500 capitalize">Role: {{ strtolower(auth()->user()->role ?? '-') }}</p>
                                </div>
                                <button type="button"
                                        class="btn btn-sm btn-outline btn-primary w-full rounded-full"
                                        @click="toggleTheme()">
                                    <span x-show="theme === 'emerald'" x-cloak>Aktifkan Mode Gelap</span>
                                    <span x-show="theme === 'emerald-dark'" x-cloak>Aktifkan Mode Terang</span>
                                </button>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-error btn-outline w-full rounded-full">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="flex-1 px-4 md:px-6 py-6">
                <div class="app-main-container">
                    @yield('content')
                </div>
            </main>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-40">
            <label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <aside class="w-72 min-h-full glass-card shadow-2xl border border-white/30 dark:border-slate-800/50">
                <x-sidebar />
            </aside>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
