@extends('layouts.admin')
@section('title', 'Data Santri')

@section('content')
<x-breadcrumb label="Data Santri" />

@php
    use App\Models\Santri;
    $totalSantri = Santri::count();
    $putra = Santri::where('jenis_kelamin', 'L')->count();
    $putri = Santri::where('jenis_kelamin', 'P')->count();
@endphp

<section class="hero-gradient text-white">
    <div class="relative z-10 grid gap-6 md:grid-cols-2">
        <div class="space-y-4">
            <span class="stat-badge">Direktori Santri</span>
            <h1 class="text-3xl md:text-4xl font-semibold leading-tight">
                Monitor populasi santri di setiap unit
            </h1>
            <p class="text-sm md:text-base text-white/85">
                Setiap perubahan akan memperbarui username wali, progres hafalan, dan penugasan halaqoh.
                Pastikan data santri terjaga agar laporan akademik dan kesantrian selalu akurat.
            </p>
            @if (in_array(strtolower(auth()->user()->role), ['superadmin','admin','admin_unit','kepala_madrasah','wakamad_kurikulum','wakamad_kesiswaan','wakamad_sarpras','bendahara']))
                <a href="{{ route('admin.santri.create') }}" class="btn btn-sm btn-primary rounded-full shadow-lg shadow-blue-500/40">
                    + Tambah Santri
                </a>
            @endif
        </div>
        <div class="glass-card bg-white/20 dark:bg-slate-900/40 border border-white/30 dark:border-slate-800/60 p-6">
            <div class="grid grid-cols-3 gap-5 text-center">
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Total Santri</p>
                    <p class="text-3xl font-semibold">{{ number_format($totalSantri) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Putra</p>
                    <p class="text-3xl font-semibold">{{ number_format($putra) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/70">Putri</p>
                    <p class="text-3xl font-semibold">{{ number_format($putri) }}</p>
                </div>
            </div>
        </div>
    </section>

@if (session('success'))
    <div class="alert alert-success mt-6 shadow-md">
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="glass-card mt-6 overflow-x-auto">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Santri</th>
                <th>NISN</th>
                <th>NISY</th>
                <th>Jenis Kelamin</th>
                <th>Unit</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($santri as $s)
                @php
                    $rowNumber = method_exists($santri, 'firstItem')
                        ? ($santri->firstItem() ?? 0) + $loop->index
                        : $loop->iteration;
                @endphp
                <tr>
                    <td>{{ $rowNumber }}</td>
                    <td class="font-semibold">{{ $s->nama }}</td>
                    <td>{{ $s->nisn ?? '—' }}</td>
                    <td>{{ $s->nisy }}</td>
                    <td>
                        <span class="badge badge-sm {{ $s->jenis_kelamin === 'L' ? 'badge-primary' : 'badge-secondary' }}">
                            {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </td>
                    <td>{{ $s->unit->nama_unit ?? '—' }}</td>
                    <td>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.santri.show', $s->id) }}" class="btn btn-xs btn-outline btn-info">Detail</a>
                            @if (in_array(strtolower(auth()->user()->role), ['superadmin','admin','admin_unit','kepala_madrasah','wakamad_kurikulum','wakamad_kesiswaan','wakamad_sarpras','bendahara']))
                                <a href="{{ route('admin.santri.edit', $s->id) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form action="{{ route('admin.santri.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus santri ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-400 py-6">Belum ada data santri.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $santri->links() }}
</div>
@endsection
