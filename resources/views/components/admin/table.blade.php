{{-- ===========================================================
📋 Komponen Table (Admin)
Tabel responsif dengan style dark mode.
=========================================================== --}}
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
            {{ $head }}
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            {{ $body }}
        </tbody>
    </table>
</div>
