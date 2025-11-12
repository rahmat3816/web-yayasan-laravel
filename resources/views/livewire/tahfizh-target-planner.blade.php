<div class="space-y-8">
    <div class="bg-white/80 dark:bg-slate-900/70 border border-emerald-200/40 dark:border-emerald-400/20 rounded-3xl shadow-xl shadow-emerald-500/10 backdrop-blur-xl p-6 md:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-widest text-emerald-500 font-semibold">Perencanaan Tahfizh</p>
                <h2 class="text-2xl md:text-3xl font-semibold text-slate-800 dark:text-white mt-1">Target Hafalan Tahunan</h2>
                <p class="text-sm text-slate-500 dark:text-slate-300 mt-2">
                    Tetapkan rentang juz → surat → ayat untuk setiap santri. Breakdown otomatis ke target bulanan, mingguan, dan harian.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div class="badge badge-outline border-emerald-200 text-emerald-600 bg-emerald-50/50 px-4 py-3 rounded-full text-xs tracking-wide uppercase">Koordinator Tahfizh</div>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success mt-6 rounded-2xl">
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="saveTarget" class="mt-8 grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Santri</span>
                    </div>
                    <select wire:model.live="santriId" class="select select-bordered bg-white/80 dark:bg-slate-900">
                        <option value="">Pilih Santri</option>
                        @foreach ($santriOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->nama }}</option>
                        @endforeach
                    </select>
                    @error('santriId') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Tahun Target</span>
                    </div>
                    <select wire:model.live="year" class="select select-bordered bg-white/80 dark:bg-slate-900">
                        @foreach ($yearOptions as $yearOption)
                            <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                        @endforeach
                    </select>
                    @error('year') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Pilih Juz</span>
                    </div>
                    <select wire:model.live="juz" class="select select-bordered bg-white/80 dark:bg-slate-900">
                        <option value="">Pilih Juz</option>
                        @for ($i = 1; $i <= 30; $i++)
                            <option value="{{ $i }}">Juz {{ $i }}</option>
                        @endfor
                    </select>
                    @error('juz') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="space-y-4">
                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Surat Awal</span>
                    </div>
                    <select wire:model.live="surahStartId" class="select select-bordered bg-white/80 dark:bg-slate-900" @disabled(!$juz)>
                        <option value="">Pilih Surat</option>
                        @foreach ($surahOptions as $option)
                            <option value="{{ $option['id'] }}">
                                {{ $option['name'] }} ({{ $option['min'] }}-{{ $option['max'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('surahStartId') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Surat Akhir</span>
                    </div>
                    <select wire:model.live="surahEndId" class="select select-bordered bg-white/80 dark:bg-slate-900" @disabled(!$juz)>
                        <option value="">Pilih Surat</option>
                        @foreach ($surahOptions as $option)
                            <option value="{{ $option['id'] }}">
                                {{ $option['name'] }} ({{ $option['min'] }}-{{ $option['max'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('surahEndId') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="form-control">
                        <div class="label">
                            <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Ayat Awal</span>
                        </div>
                        <input type="number" wire:model.live="ayatStart" min="1" class="input input-bordered bg-white/80 dark:bg-slate-900" />
                        @error('ayatStart') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                    </label>
                    <label class="form-control">
                        <div class="label">
                            <span class="label-text font-semibold text-slate-600 dark:text-slate-200">Ayat Akhir</span>
                        </div>
                        <input type="number" wire:model.live="ayatEnd" min="1" class="input input-bordered bg-white/80 dark:bg-slate-900" />
                        @error('ayatEnd') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-4">
                <button type="submit" class="btn btn-primary rounded-2xl h-12 gap-3 text-base" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Target Hafalan</span>
                    <span wire:loading class="loading loading-spinner loading-sm"></span>
                </button>
                <p class="text-xs text-slate-500">Target tersimpan otomatis per tahun & santri. Mengubah data akan memperbarui target sebelumnya.</p>
            </div>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <div class="stat bg-white/70 dark:bg-slate-900/70 rounded-2xl border border-emerald-100/40 shadow-inner">
                <div class="stat-title text-slate-500">Total Ayat</div>
                <div class="stat-value text-3xl text-emerald-600">{{ number_format($summary['total_ayat']) }}</div>
                <div class="stat-desc text-slate-400">disetorkan sepanjang tahun</div>
            </div>
            <div class="stat bg-white/70 dark:bg-slate-900/70 rounded-2xl border border-emerald-100/40 shadow-inner">
                <div class="stat-title text-slate-500">Per Bulan</div>
                <div class="stat-value text-2xl text-slate-800">{{ number_format($summary['per_bulan']) }}</div>
                <div class="stat-desc text-slate-400">ayat / bulan</div>
            </div>
            <div class="stat bg-white/70 dark:bg-slate-900/70 rounded-2xl border border-emerald-100/40 shadow-inner">
                <div class="stat-title text-slate-500">Per Minggu</div>
                <div class="stat-value text-2xl text-slate-800">{{ number_format($summary['per_minggu']) }}</div>
                <div class="stat-desc text-slate-400">ayat / minggu</div>
            </div>
            <div class="stat bg-white/70 dark:bg-slate-900/70 rounded-2xl border border-emerald-100/40 shadow-inner">
                <div class="stat-title text-slate-500">Per Hari</div>
                <div class="stat-value text-2xl text-slate-800">{{ number_format($summary['per_hari']) }}</div>
                <div class="stat-desc text-slate-400">ayat / hari</div>
            </div>
        </div>
    </div>

    <div class="bg-white/90 dark:bg-slate-900/60 backdrop-blur-xl border border-emerald-200/40 dark:border-emerald-400/20 rounded-3xl shadow-lg p-6 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Daftar Target Aktif</h3>
                <p class="text-sm text-slate-500 dark:text-slate-300">Menampilkan target per santri sesuai filter gender & unit Anda.</p>
            </div>
            <div class="badge badge-ghost px-4 py-3 rounded-full">{{ $targets->count() }} target</div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr class="text-slate-500 text-xs uppercase tracking-widest">
                        <th>Santri</th>
                        <th>Tahun</th>
                        <th>Rentang Hafalan</th>
                        <th>Total & Breakdown</th>
                        <th>Ditetapkan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($targets as $target)
                        <tr class="hover:bg-emerald-50/40">
                            <td>
                                <div class="font-semibold text-slate-800">{{ $target->santri->nama }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $target->santri->unit->nama_unit ?? 'Unit tidak diketahui' }} •
                                    {{ $target->santri->jenis_kelamin === 'P' ? 'Putri' : 'Putra' }}
                                </div>
                            </td>
                            <td class="font-semibold text-slate-700">Tahun {{ $target->tahun }}</td>
                            <td>
                                <div class="text-sm font-semibold text-emerald-600">
                                    Juz {{ $target->juz }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $target->surahStart->nama_surah ?? 'Surat ?' }} ({{ $target->ayat_start }}) →
                                    {{ $target->surahEnd->nama_surah ?? 'Surat ?' }} ({{ $target->ayat_end }})
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ number_format($target->total_ayat) }} ayat</div>
                                <div class="text-xs text-slate-500 space-x-2">
                                    <span>🌙 {{ number_format($target->target_per_bulan) }}/bln</span>
                                    <span>📅 {{ number_format($target->target_per_minggu) }}/mgg</span>
                                    <span>🕘 {{ number_format($target->target_per_hari) }}/hari</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm font-semibold text-slate-800">{{ $target->creator->name ?? 'Sistem' }}</div>
                                <div class="text-xs text-slate-500">{{ optional($target->updated_at ?? $target->created_at)->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-400">Belum ada target hafalan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
