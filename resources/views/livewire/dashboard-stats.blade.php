<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-{{ min(4, count($cards)) }}">
    @foreach ($cards as $card)
        <article class="glass-card p-5 flex items-center gap-4 backdrop-blur-xl">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl text-white bg-gradient-to-br {{ $card['gradient'] }} shadow-lg">
                {{ $card['icon'] }}
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $card['value'] }}</p>
            </div>
        </article>
    @endforeach
</div>
