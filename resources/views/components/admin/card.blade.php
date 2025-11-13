{{-- ===========================================================
 Komponen Card (Admin)
Pembungkus konten utama atau section data.
=========================================================== --}}
@props(['label' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => "bg-white dark:bg-gray-900 rounded-2xl shadow p-6 mb-8 {$class}"]) }}>
    @if($label)
        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            {!! $label !!}
        </h2>
    @endif
    {{ $slot }}
</div>
