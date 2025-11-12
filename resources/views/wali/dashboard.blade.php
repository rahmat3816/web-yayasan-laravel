@extends('layouts.wali')

@section('content')
    <x-breadcrumb />

    @php
        $cards = [
            [
                'title' => 'Progres Hafalan',
                'description' => 'Lihat perkembangan hafalan setiap anak.',
                'url' => route('wali.progres'),
                'icon' => '📈',
            ],
            [
                'title' => 'Ringkasan Hafalan',
                'description' => 'Pantau hafalan dan catatan kesehatan terbaru.',
                'url' => route('wali.hafalan'),
                'icon' => '📘',
            ],
            [
                'title' => 'Perbarui Profil',
                'description' => 'Perbarui data wali dan kontak darurat.',
                'url' => route('wali.profil'),
                'icon' => '🧾',
            ],
        ];
    @endphp

    @include('dashboard.partials.action-cards', ['cards' => $cards])

    @if (empty($rekap['total_anak']))
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow text-center">
            <h2 class="text-2xl font-semibold mb-2">Assalamu'alaikum, {{ auth()->user()->name }}</h2>
            <p class="text-gray-600 dark:text-gray-300">
                Belum ada santri yang terhubung dengan akun Anda. Silakan hubungi admin untuk memastikan data wali santri sudah sinkron.
            </p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-3 mt-2">
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Jumlah Anak</p>
                <p class="text-3xl font-bold text-blue-600">{{ $rekap['total_anak'] }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Total Setoran</p>
                <p class="text-3xl font-bold text-green-600">{{ $rekap['total_setoran'] }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Setoran Bulan Ini</p>
                <p class="text-3xl font-bold text-amber-600">{{ $rekap['bulan_ini'] }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @foreach ($anakOverview as $item)
                <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow flex flex-col">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Santri</p>
                            <h3 class="text-xl font-semibold">{{ $item['santri']->nama }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $item['santri']->unit->nama_unit ?? 'Unit belum diatur' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                            {{ $item['total_setoran'] }} setoran
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                        Terakhir setor:
                        <strong>
                            {{ $item['terakhir_setor'] ? $item['terakhir_setor']->translatedFormat('d M Y') : 'Belum ada data' }}
                        </strong>
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 bg-white dark:bg-gray-900 rounded-2xl shadow">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold">Setoran Terbaru</h3>
            </div>

            @if ($recentSetoran->isEmpty())
                <p class="p-6 text-gray-600 dark:text-gray-300">Belum ada setoran terbaru.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-left">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Santri</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($recentSetoran as $setoran)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ optional($setoran->tanggal_setor)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $setoran->santri->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $setoran->guru->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs
                                            @class([
                                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' => $setoran->status === 'lulus',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200' => $setoran->status === 'ulang',
                                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' => ! $setoran->status,
                                            ])">
                                            {{ ucfirst($setoran->status ?? 'proses') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs">{{ $setoran->catatan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <livewire:wali-hafalan-timeline />
@endsection
