@extends('layouts.admin')
@section('title', 'Program Tahfizh Qur\'an')

@php
    use Illuminate\Support\Carbon;
@endphp

@section('content')
<div class="program-tahfizh space-y-6 bg-slate-50 p-4 text-slate-900 lg:p-6">
    <x-breadcrumb label="Program Tahfizh Qur'an" />

    <x-admin.alert />

    <div class="card border border-slate-200 bg-white shadow-sm">
        <div class="card-body space-y-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Pengampu aktif</p>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ $selectedGuruName ?? 'Belum dipilih' }}</h2>
                </div>

                <form method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-end">
                    @if(!empty($selectedGuruId))
                        <input type="hidden" name="guru_id" value="{{ $selectedGuruId }}">
                    @endif

                    @if($canSwitchGuru)
                        <label class="form-control w-full min-w-[220px]">
                            <div class="label">
                                <span class="label-text text-sm font-medium text-slate-600">Pilih pengampu</span>
                            </div>
                            <select name="guru_id" class="select select-bordered bg-white">
                                @foreach($guruOptions as $guru)
                                    <option value="{{ $guru->id }}" @selected($selectedGuruId == $guru->id)>{{ $guru->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif

                    <label class="form-control w-full min-w-[160px]">
                        <div class="label">
                            <span class="label-text text-sm font-medium text-slate-600">Rentang riwayat (hari)</span>
                        </div>
                        <select name="range" class="select select-bordered bg-white">
                            @foreach($rangeOptions as $option)
                                <option value="{{ $option }}" @selected($rangeDays == $option)>{{ $option }} hari</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit" class="btn btn-primary w-full lg:w-auto">Terapkan</button>
                </form>
            </div>
            <p class="text-sm text-slate-500">Halaman ini menampilkan rangkuman target dan realisasi hafalan Qur'an berdasarkan guru pengampu serta riwayat setoran dalam {{ $rangeDays }} hari terakhir.</p>
        </div>
    </div>

    @if($statusMessage)
        <div class="card border border-amber-200 bg-amber-50/80 text-amber-900">
            <div class="card-body">
                <p class="font-semibold">{{ $statusMessage }}</p>
            </div>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="card bg-white shadow-sm border border-slate-100">
                <div class="card-body">
                    <p class="text-sm text-slate-500">Total Santri</p>
                    <h3 class="text-3xl font-semibold text-slate-900">{{ number_format($metrics['total_santri'] ?? 0) }}</h3>
                </div>
            </div>
            <div class="card bg-white shadow-sm border border-slate-100">
                <div class="card-body">
                    <p class="text-sm text-slate-500">Total Target (ayat)</p>
                    <h3 class="text-3xl font-semibold text-slate-900">{{ number_format($metrics['total_target_ayat'] ?? 0) }}</h3>
                </div>
            </div>
            <div class="card bg-white shadow-sm border border-slate-100">
                <div class="card-body">
                    <p class="text-sm text-slate-500">Realisasi (ayat)</p>
                    <h3 class="text-3xl font-semibold text-slate-900">{{ number_format($metrics['total_actual_ayat'] ?? 0) }}</h3>
                </div>
            </div>
            <div class="card bg-white shadow-sm border border-slate-100">
                <div class="card-body">
                    <p class="text-sm text-slate-500">Rata-rata Progres</p>
                    <h3 class="text-3xl font-semibold text-slate-900">{{ number_format($metrics['average_progress'] ?? 0, 1) }}%</h3>
                    <p class="text-xs text-slate-500 mt-1">Riwayat setoran tercatat: {{ number_format($metrics['setoran_count'] ?? 0) }} kali</p>
                </div>
            </div>
        </div>

        <div class="card border border-slate-200 bg-white shadow-sm">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-primary">Target Koordinator</p>
                        <h3 class="text-xl font-semibold text-slate-900">Target aktif per santri</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Halaqoh</th>
                                <th>Tahun</th>
                                <th>Juz</th>
                                <th>Surah Awal</th>
                                <th>Surah Akhir</th>
                                <th>Ayat</th>
                                <th>Total Ayat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($targetRows as $target)
                                <tr>
                                    <td class="font-semibold text-slate-800">{{ $target['santri'] }}</td>
                                    <td>{{ $target['halaqoh'] }}</td>
                                    <td>{{ $target['tahun'] }}</td>
                                    <td>{{ $target['juz'] }}</td>
                                    <td>{{ $target['surah_awal'] }}</td>
                                    <td>{{ $target['surah_akhir'] }}</td>
                                    <td>{{ $target['ayat_awal'] }} - {{ $target['ayat_akhir'] }}</td>
                                    <td>{{ number_format($target['total_ayat']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-sm text-slate-500">Belum ada target yang ditetapkan untuk santri pada guru ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border border-slate-200 bg-white shadow-sm">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-primary">Progres Target</p>
                        <h3 class="text-xl font-semibold text-slate-900">Capaian ayat per santri</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Halaqoh</th>
                                <th>Target Ayat</th>
                                <th>Realisasi Ayat</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($progressRows as $row)
                                <tr>
                                    <td class="font-semibold text-slate-800">{{ $row['santri'] }}</td>
                                    <td>{{ $row['halaqoh'] }}</td>
                                    <td>{{ number_format($row['target_ayat']) }}</td>
                                    <td>{{ number_format($row['actual_ayat']) }}</td>
                                    <td class="w-64">
                                        <div class="w-full rounded-full bg-slate-100 h-2.5">
                                            <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ min(100, $row['progress']) }}%"></div>
                                        </div>
                                        <span class="text-sm text-slate-600">{{ number_format($row['progress'], 1) }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-sm text-slate-500">Belum ada progres yang dapat dihitung.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border border-slate-200 bg-white shadow-sm">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-primary">Capaian Total</p>
                        <h3 class="text-xl font-semibold text-slate-900">Total juz, halaman, surat, dan ayat</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Total Ayat</th>
                                <th>Total Surah</th>
                                <th>Total Halaman</th>
                                <th>Total Juz</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coverageSummary as $summary)
                                <tr>
                                    <td class="font-semibold text-slate-800">{{ $summary['santri'] }}</td>
                                    <td>{{ number_format($summary['total_ayat']) }}</td>
                                    <td>{{ number_format($summary['total_surah']) }}</td>
                                    <td>{{ number_format($summary['total_halaman']) }}</td>
                                    <td>{{ number_format($summary['total_juz'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-sm text-slate-500">Belum ada data capaian hafalan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($halaqohBlocks as $block)
                @php $blockId = 'halaqoh-panels-' . $loop->index; @endphp
                <div class="card border border-slate-200 bg-white shadow-sm">
                    <div class="card-body space-y-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-sm uppercase tracking-wide text-primary">Riwayat Setoran</p>
                                <h3 class="text-xl font-semibold text-slate-900">{{ $block['nama'] }}</h3>
                            </div>
                            <div class="w-full max-w-sm">
                                <label class="label">
                                    <span class="label-text text-sm text-slate-500">Pilih santri ({{ $block['santri']->count() }})</span>
                                </label>
                                <select class="select select-bordered bg-white w-full halaqoh-santri-switch" data-target="{{ $blockId }}">
                                    @foreach($block['santri'] as $santri)
                                        <option value="{{ $santri['id'] }}">{{ $santri['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="space-y-4" id="{{ $blockId }}">
                            @foreach($block['santri'] as $santri)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 halaqoh-santri-panel" data-santri="{{ $santri['id'] }}" @if(!$loop->first) hidden @endif>
                                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="text-lg font-semibold text-slate-900">{{ $santri['nama'] }}</p>
                                            <p class="text-sm text-slate-500">{{ $santri['unit'] ?? '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            @if($santri['coverage'])
                                                <p class="text-sm text-slate-500">Total ayat</p>
                                                <p class="text-lg font-semibold text-slate-900">{{ number_format($santri['coverage']['total_ayat']) }} ayat</p>
                                            @endif
                                            @if(!is_null($santri['progress']))
                                                <p class="text-sm text-slate-500">Progress target</p>
                                                <p class="text-lg font-semibold text-emerald-600">{{ number_format($santri['progress'], 1) }}%</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-4 overflow-x-auto">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Surah</th>
                                                    <th>Ayat</th>
                                                    <th>Penilaian</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($santri['riwayat'] as $riwayat)
                                                    <tr>
                                                        <td>{{ Carbon::parse($riwayat->tanggal_setor)->format('d M Y') }}</td>
                                                        <td>{{ $riwayat->nama_surah ?? ('Surah #' . $riwayat->surah_id) }}</td>
                                                        <td>{{ $riwayat->ayah_start }} - {{ $riwayat->ayah_end }}</td>
                                                        <td>
                                                            <span class="text-xs text-slate-500">Tajwid {{ $riwayat->penilaian_tajwid ?? '-' }}, Mutqin {{ $riwayat->penilaian_mutqin ?? '-' }}, Adab {{ $riwayat->penilaian_adab ?? '-' }}</span>
                                                        </td>
                                                        <td>{{ $riwayat->catatan ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-sm text-slate-500">Belum ada setoran dalam {{ $rangeDays }} hari terakhir.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card border border-slate-200 bg-white shadow-sm">
            <div class="card-body space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-primary">Detail Setoran Terbaru</p>
                        <h3 class="text-xl font-semibold text-slate-900">Catatan terbaru</h3>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Santri</th>
                                <th>Surah</th>
                                <th>Ayat</th>
                                <th>Penilaian</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($setoranLatestPaginated ?? collect()) as $row)
                                @php
                                    $santriInfo = $santriInfoMap->get($row->santri_id);
                                @endphp
                                <tr>
                                    <td>{{ Carbon::parse($row->tanggal_setor)->format('d M Y') }}</td>
                                    <td>{{ $santriInfo->nama ?? ('Santri #' . $row->santri_id) }}</td>
                                    <td>{{ $row->nama_surah ?? ('Surah #' . $row->surah_id) }}</td>
                                    <td>{{ $row->ayah_start }} - {{ $row->ayah_end }}</td>
                                    <td class="text-xs text-slate-500">
                                        Tajwid {{ $row->penilaian_tajwid ?? '-' }},
                                        Mutqin {{ $row->penilaian_mutqin ?? '-' }},
                                        Adab {{ $row->penilaian_adab ?? '-' }}
                                    </td>
                                    <td>{{ $row->catatan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm text-slate-500">Belum ada data setoran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.program-tahfizh {
    background-color: #f8fafc;
    color: #0f172a;
}
.program-tahfizh .card {
    background-color: #ffffff;
    color: #0f172a;
}
.program-tahfizh .text-slate-500,
.program-tahfizh .text-gray-500 {
    color: #334155 !important;
}
.program-tahfizh .table th,
.program-tahfizh .table td {
    color: #0f172a;
}
.program-tahfizh .table thead th {
    background-color: #e2e8f0;
}
.program-tahfizh .select,
.program-tahfizh .input,
.program-tahfizh .textarea {
    background-color: #ffffff;
    color: #0f172a;
}
.program-tahfizh .pagination-wrapper nav {
    display: inline-flex;
    gap: 0.25rem;
    align-items: center;
}
.program-tahfizh .pagination-wrapper nav > * {
    border-radius: 999px;
    border: 1px solid #cbd5f5;
    padding: 0.4rem 0.85rem;
    background-color: #ffffff;
    color: #0f172a;
    font-size: 0.875rem;
    font-weight: 500;
}
.program-tahfizh .pagination-wrapper nav span[aria-current],
.program-tahfizh .pagination-wrapper nav a:hover {
    background-color: #1d4ed8;
    color: #ffffff;
    border-color: #1d4ed8;
}
.program-tahfizh .pagination-wrapper nav svg {
    width: 1rem;
    height: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.halaqoh-santri-switch').forEach(function (select) {
        const targetId = select.dataset.target;
        const syncPanels = () => {
            const current = select.value;
            document.querySelectorAll('#' + targetId + ' .halaqoh-santri-panel').forEach(function (panel) {
                panel.hidden = panel.dataset.santri !== current;
            });
        };
        select.addEventListener('change', syncPanels);
        syncPanels();
    });
});
</script>
@endpush
