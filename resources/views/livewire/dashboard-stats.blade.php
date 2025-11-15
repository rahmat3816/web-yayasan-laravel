@php
    $chunkedCards = collect($cards)->chunk(3);
@endphp

<div class="flex flex-col gap-2 sm:gap-3 lg:gap-4">
    @foreach ($chunkedCards as $row)
        <div class="grid gap-2 sm:gap-3 lg:gap-4 md:grid-cols-{{ min(3, $row->count()) }}">
            @foreach ($row as $card)
                <article class="glass-card px-5 py-4 flex items-center gap-4 backdrop-blur-xl">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white bg-gradient-to-br {{ $card['gradient'] }} shadow-lg">
                        <x-filament::icon :icon="$card['icon']" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                        <p class="text-3xl font-semibold text-slate-900 dark:text-white leading-tight">{{ $card['value'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endforeach
</div>
