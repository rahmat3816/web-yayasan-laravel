@extends('layouts.app')

@section('content')
<div class="space-y-6 p-4 lg:p-6">
    <x-admin.alert />

    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-500 via-cyan-500 to-blue-600 text-white shadow-xl">
        <div class="absolute inset-0 opacity-30 blur-3xl bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.35),_transparent_55%)]"></div>
        <div class="relative flex flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-white/70">Halaqoh Hafalan</p>
                <h1 class="mt-2 text-3xl font-bold">Setoran Hafalan Quran</h1>
                <p class="mt-3 text-white/80">
                    Pantau progres santri, susun jadwal, serta tambah setoran baru tanpa menyentuh logika penilaian yang sudah baku.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="badge badge-outline badge-lg border-white text-white">
                        Halaqoh: {{ $halaqoh->nama_halaqoh ?? '-' }}
                    </span>
                    <span class="badge badge-outline badge-lg border-white text-white">
                        Guru: {{ $halaqoh->guru->nama ?? 'Belum ditentukan' }}
                    </span>
                    <span class="badge badge-outline badge-lg border-white text-white">
                        Unit: {{ $halaqoh->unit->nama_unit ?? '-' }}
                    </span>
                </div>
            </div>

            @if($isSuper && $allHalaqoh->count())
                <div class="rounded-2xl bg-white/15 p-4 backdrop-blur">
                    <form method="GET" action="{{ route('guru.setoran.index') }}" class="flex flex-col gap-3">
                        <label class="text-sm font-semibold text-white">Pilih Halaqoh</label>
                        <select name="halaqoh_id" class="select select-bordered w-full bg-white/90 text-gray-900">
                            @foreach($allHalaqoh as $h)
                                <option value="{{ $h->id }}" @selected(request('halaqoh_id', $halaqoh->id ?? 0) == $h->id)>
                                    H{{ $h->id }} — {{ $h->guru->nama ?? 'Belum Ada Guru' }} ({{ $h->unit->nama_unit ?? 'Unit '.$h->unit_id }})
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary border-none bg-white/90 text-gray-900 hover:bg-white">
                            Tampilkan
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3 text-gray-900">
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Halaman</p>
                        <p class="text-3xl font-semibold text-gray-900">{{ $rekap['total_halaman'] }}</p>
                    </div>
                    <div class="badge badge-primary badge-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5.5A2.5 2.5 0 015.5 3H10v15H5.5A2.5 2.5 0 013 15.5v-10zM21 5.5A2.5 2.5 0 0018.5 3H14v15h4.5A2.5 2.5 0 0021 15.5v-10z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Akumulasi halaman hafalan dari seluruh setoran.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Juz</p>
                        <p class="text-3xl font-semibold text-gray-900">{{ $rekap['total_juz'] }}</p>
                    </div>
                    <div class="badge badge-secondary badge-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2M12 3a9 9 0 110 18 9 9 0 010-18z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Jumlah juz yang sudah dituntaskan.</p>
            </div>
        </div>
        <div class="card glass shadow-lg bg-white/90">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Surah</p>
                        <p class="text-3xl font-semibold text-gray-900">{{ $rekap['total_surah'] }}</p>
                    </div>
                    <div class="badge badge-accent badge-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 9l1.5-4L9 9l4 1.5L9 12l-1.5 4L6 12l-4-1.5L6 9zm10 4l1-3 1 3 3 1-3 1-1 3-1-3-3-1 3-1z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Surah yang telah selesai disetorkan.</p>
            </div>
        </div>
    </div>

    <div class="card bg-white/90 shadow-xl text-gray-900">
        <div class="card-body">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="card-title text-lg font-semibold text-gray-900">Santri dalam Halaqoh</h2>
                    <p class="text-sm text-gray-500">Setiap santri memiliki kartu progres lengkap dengan tombol setoran baru.</p>
                </div>
                <a href="{{ route('guru.setoran.rekap', array_filter(['halaqoh_id' => request('halaqoh_id', $halaqoh->id ?? null)])) }}"
                   class="btn btn-ghost gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 19V9m5 10V5m5 14V11m5 8V7" />
                    </svg>
                    Lihat Rekap Detail
                </a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($santri as $s)
                    <div class="group rounded-2xl border border-gray-100 bg-white p-4 shadow transition hover:-translate-y-1 hover:border-primary/40 hover:shadow-2xl">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Santri</p>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $s->nama }}</h3>
                            </div>
                            <span class="badge {{ $s->jenis_kelamin === 'P' ? 'badge-error' : 'badge-warning' }} text-white">
                                {{ $s->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-Laki' }}
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-gray-600">
                            Detail progres lengkap tersedia pada menu Rekap Setoran.
                        </p>
                        <div class="mt-4">
                            @if(!$isSuper)
                                <a href="{{ route('guru.setoran.create', $s->id) }}"
                                   class="btn btn-primary btn-block shadow">
                                    + Setor Baru
                                </a>
                            @else
                                <span class="text-xs text-gray-500">Superadmin hanya mode pantau.</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-base-200 bg-base-100/70 p-8 text-center text-sm text-gray-500">
                        Belum ada santri pada halaqoh ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
