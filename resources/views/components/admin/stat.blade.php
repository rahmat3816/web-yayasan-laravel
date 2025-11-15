@props(['label', 'value' => 0, 'color' => 'indigo', 'icon' => ''])

@php
    $colorMap = [
        'indigo' => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300',
        'teal'   => 'bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300',
        'amber'  => 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300',
        'pink'   => 'bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300',
        'green'  => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
        'blue'   => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300',
        'fuchsia' => 'bg-fuchsia-100 dark:bg-fuchsia-900 text-fuchsia-700 dark:text-fuchsia-300',
        'sky'     => 'bg-sky-100 dark:bg-sky-900 text-sky-700 dark:text-sky-300',
    ];

    $style = $colorMap[$color] ?? $color;
@endphp

<div class="p-4 rounded-lg shadow text-center {{ $style }}">
    <div class="text-sm font-medium">{{ $icon }} {{ $label }}</div>
    <div class="text-2xl font-bold mt-1">{{ $value }}</div>
</div>
