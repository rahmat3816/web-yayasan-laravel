{{-- ==============================
📗 Sidebar - Optimized Version
============================== --}}
@php
    $is = fn(string $pattern) => request()->is($pattern);
    $linkBase = 'block px-3 py-2 rounded-lg transition';
    $active   = 'bg-blue-100 dark:bg-blue-900 font-semibold text-blue-700 dark:text-blue-300';
    $hover    = 'hover:bg-blue-50 dark:hover:bg-gray-700';

    $user = auth()->user();
    $panelCatalog = config('jabatan.panels', []);
    $panelSections = [];
    $shouldAppendSetoranEntries = false;

    if ($user) {
        $canViewAll = $user->hasRole('superadmin');
        $linkedGuruId = $user->linked_guru_id ?? null;
        if (!$linkedGuruId && method_exists($user, 'ensureLinkedGuruId')) {
            $linkedGuruId = $user->ensureLinkedGuruId();
        }
        $isKoordinatorTahfizh = $user->hasRole(['koordinator_tahfizh_putra','koordinator_tahfizh_putri']);
        if (!$isKoordinatorTahfizh && ($user->hasRole('superadmin') || ($linkedGuruId && \App\Models\Halaqoh::where('guru_id', $linkedGuruId)->exists()))) {
            $shouldAppendSetoranEntries = true;
        }

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

        foreach ($panelCatalog as $categoryKey => $category) {
            $entries = [];

            foreach ($category['entries'] as $entry) {
                $roles = (array) ($entry['roles'] ?? []);
                $hasAccess = $canViewAll;

                if (!$hasAccess && !empty($roles)) {
                    $hasAccess = $user->hasRole($roles) || $user->hasJabatan($roles);
                }

                if ($hasAccess) {
                    $entries[] = [
                        'label' => $entry['short_label'] ?? $entry['title'],
                        'url' => $resolveUrl($entry),
                        'pattern' => $entry['pattern'] ?? '*',
                        'icon' => $entry['icon'] ?? '->',
                    ];
                }
            }

            if ($categoryKey === 'halaqoh_hafalan' && $shouldAppendSetoranEntries) {
                $needsSetoranLink = !$user->hasRole(['guru', 'wali_kelas']);
                if ($needsSetoranLink) {
                    $entries[] = [
                        'label' => 'Setoran Hafalan',
                        'url' => route('guru.setoran.index'),
                        'pattern' => 'guru/setoran*',
                        'icon' => '🕌',
                    ];
                }

                $entries[] = [
                    'label' => 'Rekap Setoran',
                    'url' => route('guru.setoran.rekap'),
                    'pattern' => 'guru/setoran/rekap',
                    'icon' => '📑',
                ];
                $shouldAppendSetoranEntries = false;
            }

            if (!empty($entries)) {
                $panelSections[] = [
                    'label' => $category['label'],
                    'items' => $entries,
                ];
            }
        }
    }
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

            @if (!empty($panelSections))
                <li class="mt-4 px-3 text-xs uppercase tracking-wider text-gray-400">Akses Jabatan</li>
                <div class="px-2 space-y-3">
                    @foreach ($panelSections as $section)
                        <div x-data="{ open: true }" class="bg-gradient-to-br from-white via-blue-50 to-white dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 border border-white/60 dark:border-gray-700 rounded-2xl shadow-sm">
                            <button type="button"
                                    class="w-full flex items-center justify-between px-3 py-2 text-[12px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300"
                                    @click="open = !open">
                                <span>{{ $section['label'] }}</span>
                                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a.75.75 0 0 1 .75.75v3.5h3.5a.75.75 0 0 1 0 1.5h-3.5v3.5a.75.75 0 0 1-1.5 0v-3.5h-3.5a.75.75 0 0 1 0-1.5h3.5v-3.5A.75.75 0 0 1 10 5Z" clip-rule="evenodd" />
                                </svg>
                                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 0 1.5h-8.5A.75.75 0 0 1 5 9.25Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul x-show="open" x-collapse class="px-2 pb-3 space-y-1 text-sm">
                                @foreach ($section['items'] as $module)
                                    <li>
                                        <a href="{{ $module['url'] }}"
                                           class="{{ $linkBase }} flex items-center gap-2 {{ $is($module['pattern']) ? $active : $hover }}">
                                            <span class="text-lg">{{ $module['icon'] }}</span>
                                            <span class="flex-1">{{ $module['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif

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
