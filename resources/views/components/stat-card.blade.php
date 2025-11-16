@props([
    'label',
    'value',
    'note' => null,
    'icon' => null,
])

<div {{ $attributes->class('rounded-2xl border border-gray-100 dark:border-white/10 bg-white/90 dark:bg-white/5 p-4 shadow-sm space-y-2') }}>
    <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 font-semibold">{{ $label }}</p>
    <div class="flex items-center gap-3">
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
        @if ($icon)
            <x-filament::icon :icon="$icon" class="h-6 w-6 text-gray-400 dark:text-white/70" />
        @endif
    </div>
    @if ($note)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $note }}</p>
    @endif
</div>
