{{-- ===========================================================
🔔 Komponen Alert (Admin)
Menampilkan notifikasi sukses, error, atau info
=========================================================== --}}
@php
    $type = $type ?? (
        session()->has('success') ? 'success' :
        (session()->has('error') ? 'error' :
        (session()->has('warning') ? 'warning' : null))
    );

    $message = $message ?? (
        session('success') ?? session('error') ?? session('warning') ?? ''
    );

    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-800 dark:bg-green-900/30 dark:text-green-200 dark:border-green-600',
        'error'   => 'bg-red-100 border-red-400 text-red-800 dark:bg-red-900/30 dark:text-red-200 dark:border-red-600',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200 dark:border-yellow-600',
        'info'    => 'bg-blue-100 border-blue-400 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-600',
    ];
@endphp

@if($type && $message)
    <div class="mb-5 px-4 py-3 border-l-4 rounded-lg shadow-sm {{ $colors[$type] ?? $colors['info'] }}">
        <div class="flex items-center gap-2">
            @switch($type)
                @case('success') <span>✅</span> @break
                @case('error')   <span>❌</span> @break
                @case('warning') <span>⚠️</span> @break
                @default         <span>ℹ️</span>
            @endswitch
            <p class="font-medium">{{ $message }}</p>
        </div>
    </div>
@endif
