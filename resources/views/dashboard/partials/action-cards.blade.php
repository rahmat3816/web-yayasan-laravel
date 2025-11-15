@if (!empty($cards))
    <div class="action-card-grid grid gap-5 md:grid-cols-2 lg:grid-cols-3 my-8">
        @foreach ($cards as $card)
            <a href="{{ $card['url'] ?? '#' }}"
               class="glass-card p-5 flex flex-col justify-between hover:-translate-y-1 hover:shadow-2xl transition duration-300 group">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-2xl bg-gradient-to-br from-amber-500/90 to-orange-400/80 text-white text-xl shadow-lg">
                        {{ $card['icon'] ?? '' }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $card['title'] ?? 'Tugas' }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-300 mt-1">{{ $card['description'] ?? '' }}</p>
                    </div>
                </div>
                <span class="mt-6 text-sm font-semibold text-primary inline-flex items-center gap-1 group-hover:gap-2 transition">
                    Mulai Tugas ->
                </span>
            </a>
        @endforeach
    </div>
@endif
