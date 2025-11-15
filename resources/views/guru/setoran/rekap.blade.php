@extends('layouts.app')

@section('title', 'Rekap Setoran Hafalan')

@section('content')
<div class="space-y-6 p-4 lg:p-6">
    <div class="relative overflow-hidden rounded-3xl border border-sky-100 bg-gradient-to-r from-sky-100 via-cyan-50 to-white text-gray-900 shadow-xl">
        <div class="absolute inset-0 opacity-60 blur-3xl bg-[radial-gradient(circle_at_top,_rgba(93,176,255,0.35),_transparent_55%)]"></div>
        <div class="relative px-6 py-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm uppercase tracking-[0.4em] text-sky-600">Rekap Detail</p>
            <div>
                <h1 class="text-3xl font-semibold">Ringkasan Setoran Halaqoh</h1>
                <p class="mt-3 max-w-2xl text-gray-600">
                    Semua data di bawah bersumber langsung dari log Setoran Hafalan dan hanya disajikan ulang agar mudah dianalisis.
                </p>
                <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold">
                    <span class="badge badge-outline border-sky-500 text-sky-700">Halaqoh: {{ $halaqoh->nama_halaqoh ?? '-' }}</span>
                    <span class="badge badge-outline border-sky-500 text-sky-700">Guru: {{ $halaqoh->guru->nama ?? 'Belum ditentukan' }}</span>
                    <span class="badge badge-outline border-sky-500 text-sky-700">Unit: {{ $halaqoh->unit->nama_unit ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3 text-gray-900">
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Juz</p>
                        <p class="text-3xl font-semibold">{{ $rekap['total_juz'] ?? 0 }}</p>
                    </div>
                    <span class="rounded-full bg-sky-100 p-3 text-sky-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2M12 3a9 9 0 110 18 9 9 0 010-18z" />
                        </svg>
                    </span>
                </div>
                <p class="text-xs text-gray-500">Juz yang sudah dilaporkan dari setoran ini.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Surah</p>
                        <p class="text-3xl font-semibold">{{ $rekap['total_surah'] ?? 0 }}</p>
                    </div>
                    <span class="rounded-full bg-amber-100 p-3 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l1.5-4L9 9l4 1.5L9 12l-1.5 4L6 12l-4-1.5L6 9zm10 4l1-3 1 3 3 1-3 1-1 3-1-3-3-1 3-1z" />
                        </svg>
                    </span>
                </div>
                <p class="text-xs text-gray-500">Surah yang telah dilewati santri.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Jumlah Setoran</p>
                        <p class="text-3xl font-semibold">{{ $data->total() }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 p-3 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>
                <p class="text-xs text-gray-500">Total baris data yang tercatat.</p>
            </div>
        </div>
    </div>

    <div class="card bg-white/95 shadow-2xl text-gray-900">
        <div class="card-body overflow-x-auto">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="card-title text-lg font-semibold">Detail Setoran</h2>
                <a href="{{ route('guru.setoran.index', request()->only('halaqoh_id')) }}" class="btn btn-ghost gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7m-7 7h18" />
                    </svg>
                    Kembali ke daftar santri
                </a>
            </div>

            <div class="mt-4 overflow-x-auto rounded-2xl border border-gray-100">
                <table class="min-w-full text-sm text-gray-900">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Santri</th>
                            <th class="px-4 py-3 text-left">Surah</th>
                            <th class="px-4 py-3 text-left">Rentang Ayat</th>
                            <th class="px-4 py-3 text-left">Juz</th>
                            <th class="px-4 py-3 text-left">Tajwid</th>
                            <th class="px-4 py-3 text-left">Mutqin</th>
                            <th class="px-4 py-3 text-left">Adab</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($data as $index => $h)
                            <tr class="border-t border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-gray-500">
                                    {{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ \Carbon\Carbon::parse($h->tanggal_setor)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3">{{ $h->santri->nama ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-700">
                                    {{ $h->surah_id ? sprintf('%03d', $h->surah_id) : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    Ayat {{ $h->ayah_start }}-{{ $h->ayah_end }}
                                </td>
                                <td class="px-4 py-3">{{ $h->juz_start }}</td>
                                <td class="px-4 py-3 font-semibold text-emerald-700">{{ $h->penilaian_tajwid ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $h->penilaian_mutqin ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-sky-700">{{ $h->penilaian_adab ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $h->catatan ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada data setoran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $data->onEachSide(1)->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
