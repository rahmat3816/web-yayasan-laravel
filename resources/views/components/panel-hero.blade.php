@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'badges' => [],
])

<div {{ $attributes->class('rounded-3xl bg-gradient-to-br from-blue-700 via-sky-500 to-teal-400 text-white p-6 shadow-2xl overflow-hidden relative') }}>
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-3">
            @if ($eyebrow)
                <p class="text-xs uppercase tracking-[0.35em] text-white/80 font-semibold">{{ $eyebrow }}</p>
            @endif
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight">{{ $title }}</h1>
            @if ($subtitle)
                <p class="text-sm text-white/85 max-w-2xl">{{ $subtitle }}</p>
            @endif
            @if (!empty($badges))
                <div class="flex flex-wrap gap-2 text-sm font-medium">
                    @foreach ($badges as $badge)
                        <span class="px-3 py-1 rounded-full border border-white/30 bg-white/10 backdrop-blur-xl">
                            {{ $badge }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-col gap-3 w-full md:w-auto">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if (trim($slot))
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
