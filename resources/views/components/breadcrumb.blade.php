{{-- ===============================================
 Komponen Breadcrumb (v3 Final)
Aman dari redeclare & konsisten dengan layout admin
================================================ --}}
@php
    // Ambil segment URL aktif, misal: ['admin', 'santri', 'edit']
    $segments = request()->segments();
    $paths = [];
    $build = '';

    // Bangun array URL untuk setiap segment
    foreach ($segments as $segment) {
        $build .= '/' . $segment;
        $paths[$segment] = $build;
    }

    //  Cegah duplikasi deklarasi fungsi
    if (!function_exists('formatSegment')) {
        function formatSegment($segment) {
            return ucfirst(str_replace(['-', '_'], ' ', $segment));
        }
    }
@endphp

<nav class="text-sm text-gray-600 dark:text-gray-300 mb-4" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center space-x-2">
        {{--  Dashboard --}}
        <li>
            <a href="/dashboard" class="hover:text-blue-600 flex items-center">
                 <span class="ml-1">Dashboard</span>
            </a>
        </li>

        {{--  Loop untuk setiap segment --}}
        @foreach ($paths as $segment => $url)
            <li class="text-gray-400">></li>
            <li>
                @if ($loop->last)
                    {{-- Segment terakhir = halaman aktif --}}
                    <span class="font-semibold text-blue-600 dark:text-blue-400 capitalize">
                        {{ formatSegment($segment) }}
                    </span>
                @else
                    {{-- Segment sebelumnya = link navigasi --}}
                    <a href="{{ $url }}" class="hover:text-blue-600 capitalize">
                        {{ formatSegment($segment) }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>

    {{--  Judul Halaman --}}
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mt-2 capitalize">
        {{ formatSegment(last($segments) ?? 'Dashboard') }}
    </h2>
</nav>
