{{-- =======================================================
Komponen Notifikasi Global <x-admin.alert />
======================================================= --}}
@props(['dismissible' => true])

@php
    $alerts = [];

    if (session('success')) $alerts[] = ['type' => 'success', 'message' => session('success')];
    if (session('error'))   $alerts[] = ['type' => 'error',   'message' => session('error')];
    if (session('warning')) $alerts[] = ['type' => 'warning', 'message' => session('warning')];

    if ($errors->any()) {
        $msg = '<ul class="list-disc list-inside space-y-1 mt-1">';
        foreach ($errors->all() as $err) {
            $msg .= "<li>{$err}</li>";
        }
        $msg .= '</ul>';
        $alerts[] = ['type' => 'error', 'message' => $msg];
    }

    $styles = [
        'success' => [
            'classes' => 'bg-emerald-50/95 border border-emerald-200 text-emerald-800 dark:bg-emerald-900/50 dark:border-emerald-800/60 dark:text-emerald-100',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" /></svg>',
        ],
        'error' => [
            'classes' => 'bg-rose-50/95 border border-rose-200 text-rose-800 dark:bg-rose-900/50 dark:border-rose-800/60 dark:text-rose-100',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        ],
        'warning' => [
            'classes' => 'bg-amber-50/95 border border-amber-200 text-amber-800 dark:bg-amber-900/50 dark:border-amber-800/60 dark:text-amber-100',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
        ],
    ];
@endphp

@if(!empty($alerts))
    <div class="mb-6 space-y-3">
        @foreach($alerts as $alert)
            @php
                $s = $styles[$alert['type']] ?? $styles['success'];
            @endphp
            <div role="alert" class="flex items-start gap-3 rounded-2xl px-4 py-3 shadow-sm backdrop-blur {{ $s['classes'] }}">
                <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-white/50 text-current dark:bg-white/10">
                    {!! $s['icon'] !!}
                </span>
                <div class="flex-1 text-sm leading-relaxed">
                    {!! $alert['message'] !!}
                </div>
                @if($dismissible)
                    <button type="button"
                        onclick="this.closest('[role=alert]').remove()"
                        class="ml-3 rounded-full p-1 text-sm font-semibold opacity-60 transition hover:bg-white/60 hover:opacity-100 dark:hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endforeach
    </div>
@endif
