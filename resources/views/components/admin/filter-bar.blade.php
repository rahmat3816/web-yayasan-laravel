{{-- ===========================================================
 Komponen Filter Bar (Admin)
Digunakan untuk form pencarian & filter laporan.
=========================================================== --}}
@props(['action' => '#', 'resetRoute' => null])

<form method="GET" action="{{ $action }}" class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{ $fields }}
    </div>

    <div class="mt-4 flex gap-3">
        <button type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
             Tampilkan
        </button>

        @if($resetRoute)
            <a href="{{ $resetRoute }}"
               class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-400">
                 Reset
            </a>
        @endif
    </div>
</form>
