@extends('layouts.wali')

@section('content')
    <x-breadcrumb />

    @if ($anakOverview->isEmpty())
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow text-center">
            <p class="text-gray-600 dark:text-gray-300">
                Belum ada data hafalan untuk ditampilkan.
            </p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($anakOverview as $item)
                <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Santri</p>
                            <h3 class="text-xl font-semibold">{{ $item['santri']->nama }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $item['santri']->unit->nama_unit ?? 'Unit belum diatur' }}
                            </p>
                        </div>
                        <span class="text-3xl font-bold text-blue-600">{{ $item['total_setoran'] }}</span>
                    </div>
                    <dl class="mt-4 text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <div class="flex justify-between">
                            <dt>Total setoran lulus</dt>
                            <dd class="font-semibold text-green-600">{{ $item['total_lulus'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Terakhir setor</dt>
                            <dd>
                                {{ $item['terakhir_setor'] ? $item['terakhir_setor']->translatedFormat('d M Y') : 'Belum ada data' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <h3 class="text-lg font-semibold mb-4">Trend 6 Bulan Terakhir</h3>
                @if ($monthlyTrend->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Belum ada data.</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($monthlyTrend as $trend)
                            <li class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-1">
                                <span>{{ $trend->label }}</span>
                                <span class="font-semibold text-blue-600">{{ $trend->total }} setoran</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <h3 class="text-lg font-semibold mb-4">10 Setoran Terbaru</h3>
                @if ($recentSetoran->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Belum ada data.</p>
                @else
                    <ul class="space-y-3 text-sm">
                        @foreach ($recentSetoran as $setoran)
                            <li class="flex justify-between">
                                <div>
                                    <p class="font-semibold">{{ $setoran->santri->nama ?? '-' }}</p>
                                    <p class="text-gray-500">
                                        {{ optional($setoran->tanggal_setor)->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs
                                    @class([
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' => $setoran->status === 'lulus',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200' => $setoran->status === 'ulang',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' => ! $setoran->status,
                                    ])">
                                    {{ ucfirst($setoran->status ?? 'proses') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
@endsection
