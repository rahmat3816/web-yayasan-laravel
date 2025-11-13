{{-- ===========================================================
 Komponen Table (Admin)
Versi props modern (tanpa $head/$body).
=========================================================== --}}
@props(['headers' => []])

<div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-900">
    <table class="min-w-full text-sm border-collapse">
        {{-- Header --}}
        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase tracking-wide text-xs font-semibold">
            <tr>
                @foreach ($headers as $h)
                    <th class="px-6 py-3 text-left border-b border-gray-200 dark:border-gray-700">
                        {{ $h }}
                    </th>
                @endforeach
            </tr>
        </thead>

        {{-- Body --}}
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-300">
            {{ $slot }}
        </tbody>
    </table>
</div>
