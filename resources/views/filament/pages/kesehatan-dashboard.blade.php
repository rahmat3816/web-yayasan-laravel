<x-filament-panels::page class="space-y-6">
    <x-filament::section>
        <x-slot name="heading">Ringkasan Kesehatan</x-slot>

        <form method="GET" class="grid gap-3 sm:grid-cols-3 mb-4">
            <label class="grid gap-1 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Tahun</span>
                <select name="filterYear" onchange="this.form.submit()" class="fi-input block w/full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected(($filterYear ?? request('filterYear')) == $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Bulan</span>
                <select name="filterMonth" onchange="this.form.submit()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="" @selected(($filterMonth ?? request('filterMonth')) === null || ($filterMonth ?? request('filterMonth')) === '')>Semua Bulan</option>
                    @foreach($months as $num => $label)
                        <option value="{{ $num }}" @selected(($filterMonth ?? request('filterMonth')) == $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Asrama</span>
                <select name="filterAsramaId" onchange="this.form.submit()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    <option value="" @selected(($filterAsramaId ?? request('filterAsramaId')) === null || ($filterAsramaId ?? request('filterAsramaId')) === '')>Semua Asrama</option>
                    @foreach($asramas as $id => $nama)
                        <option value="{{ $id }}" @selected(($filterAsramaId ?? request('filterAsramaId')) == $id)>{{ $nama }}</option>
                    @endforeach
                </select>
            </label>
        </form>

        <x-filament-widgets::widgets
            :widgets="[
                \App\Filament\Widgets\KesehatanSummaryWidget::class,
                \App\Filament\Widgets\KesehatanKasusPerHari::class,
                \App\Filament\Widgets\KesehatanFrekuensiSantri::class,
                \App\Filament\Widgets\KesehatanKeluhanTop::class,
                \App\Filament\Widgets\KesehatanKasusPerAsrama::class,
            ]"
            :columns="['default' => 1]"
            :data="$this->getWidgetData()"
        />
    </x-filament::section>
</x-filament-panels::page>
