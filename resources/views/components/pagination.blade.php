@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 text-sm text-gray-500 md:flex-row md:items-center md:justify-between">
        <div>
            Menampilkan
            <span class="font-semibold text-gray-900">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-semibold text-gray-900">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-gray-900">{{ $paginator->total() }}</span>
            setoran.
        </div>

        <div class="join border border-gray-200 bg-white shadow-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-ghost btn-sm text-gray-400" disabled>
                    <span aria-hidden="true"><</span>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="join-item btn btn-ghost btn-sm text-gray-600 hover:bg-gray-100"
                   rel="prev">
                    <
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="join-item btn btn-ghost btn-sm text-gray-400" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="join-item btn btn-sm bg-sky-600 text-white border-sky-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="join-item btn btn-ghost btn-sm text-gray-600 hover:bg-gray-100">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="join-item btn btn-ghost btn-sm text-gray-600 hover:bg-gray-100"
                   rel="next">
                    >
                </a>
            @else
                <button class="join-item btn btn-ghost btn-sm text-gray-400" disabled>
                    <span aria-hidden="true">></span>
                </button>
            @endif
        </div>
    </div>
@endif
