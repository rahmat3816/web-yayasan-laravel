@extends('layouts.wali')

@section('title', 'Progres Hafalan Anak')

@section('content')
    <x-breadcrumb />

    @if ($santriList->isEmpty())
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow text-center">
            <p class="text-gray-600 dark:text-gray-300">
                Belum ada santri yang terhubung dengan akun Anda. Silakan hubungi admin untuk memastikan data sudah sinkron dengan Filament.
            </p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Setoran Ditampilkan</p>
                <p class="text-3xl font-bold text-blue-600">{{ $summary['total'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Status Lulus</p>
                <p class="text-3xl font-bold text-green-600">{{ $statusBreakdown['lulus'] ?? 0 }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
                <p class="text-sm text-gray-500">Perlu Ulang</p>
                <p class="text-3xl font-bold text-amber-600">{{ $statusBreakdown['ulang'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 p-4 bg-white dark:bg-gray-900 rounded-2xl shadow">
            <form method="GET" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Santri</label>
                    <select name="santri_id" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua Anak</option>
                        @foreach ($santriList as $santri)
                            <option value="{{ $santri->id }}"
                                @selected((int) ($filters['santri_id'] ?? 0) === $santri->id)>
                                {{ $santri->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Status</label>
                    <select name="status" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:text-gray-200">
                        <option value="">Semua Status</option>
                        @foreach (['lulus' => 'Lulus', 'ulang' => 'Perlu Ulang', 'proses' => 'Proses'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Bulan</label>
                    <input type="month"
                           name="bulan"
                           value="{{ $filters['bulan'] ?? '' }}"
                           class="w-full rounded border-gray-300 dark:bg-gray-800 dark:text-gray-200" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Data per Halaman</label>
                    <select name="per_page" class="w-full rounded border-gray-300 dark:bg-gray-800 dark:text-gray-200">
                        @foreach ([10, 25, 50] as $perPage)
                            <option value="{{ $perPage }}" @selected(($filters['per_page'] ?? 10) == $perPage)>
                                {{ $perPage }} baris
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-3 justify-end">
                    <a href="{{ route('wali.progres') }}"
                       class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800">
                        Reset
                    </a>
                    <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-white dark:bg-gray-900 rounded-2xl shadow">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-semibold">Daftar Setoran</h3>
            </div>

            @if ($hafalan->isEmpty())
                <p class="p-6 text-gray-600 dark:text-gray-300">Belum ada data setoran dengan filter ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-left">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Santri</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Juz &amp; Surah</th>
                                <th class="px-4 py-3">Ayat</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($hafalan as $setoran)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ optional($setoran->tanggal_setor)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">{{ $setoran->santri->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $setoran->guru->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        Juz {{ $setoran->juz_start ?? '-' }} - Surah {{ $setoran->surah_id ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $setoran->ayah_start ?? '-' }} s/d {{ $setoran->ayah_end ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs
                                            @class([
                                                'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' => $setoran->status === 'lulus',
                                                'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200' => $setoran->status === 'ulang',
                                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' => ! $setoran->status || $setoran->status === 'proses',
                                            ])">
                                            {{ ucfirst($setoran->status ?? 'proses') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs">
                                        {{ $setoran->catatan ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $hafalan->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
