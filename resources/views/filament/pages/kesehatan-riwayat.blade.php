<x-filament-panels::page class="space-y-6">
    <x-filament::section>
        <x-slot name="heading">Riwayat Kesehatan Santri</x-slot>
        <p class="text-sm text-gray-500 dark:text-gray-400">Lihat log kesehatan per santri dengan memilih santri, tahun, dan bulan.</p>

        <div class="grid gap-4 sm:grid-cols-2 mt-4 text-sm">
            <label class="grid gap-1">
                <span class="text-gray-600 dark:text-gray-300">Asrama</span>
                <select wire:model.live="filterAsramaId" class="fi-input block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900" @if($asramaLocked) disabled @endif>
                    <option value="">Semua Asrama</option>
                    @foreach($asramaOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
                @if($asramaLocked)
                    <span class="text-xs text-gray-500">Terkunci ke asrama musyrif aktif.</span>
                @endif
            </label>
            <label class="grid gap-1">
                <span class="text-gray-600 dark:text-gray-300">Santri</span>
                <select wire:model.live="filterSantriId" class="fi-input block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Pilih Santri</option>
                    @foreach($santriOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">
                <span class="text-gray-600 dark:text-gray-300">Tahun</span>
                <select wire:model.live="filterYear" class="fi-input block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Semua Tahun</option>
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">
                <span class="text-gray-600 dark:text-gray-300">Bulan</span>
                <select wire:model.live="filterMonth" class="fi-input block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="">Semua Bulan</option>
                    @foreach($monthOptions as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
