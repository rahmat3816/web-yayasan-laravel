<?php

namespace App\Livewire;

use App\Http\Controllers\Guru\SetoranHafalanController;
use App\Models\HafalanTarget;
use App\Models\Santri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TahfizhTargetPlanner extends Component
{
    public ?int $santriId = null;
    public int $year;
    public ?int $juz = null;
    public ?int $surahStartId = null;
    public ?int $surahEndId = null;
    public ?int $ayatStart = null;
    public ?int $ayatEnd = null;
    public array $surahOptions = [];
    public array $summary = [
        'total_ayat' => 0,
        'per_bulan' => 0,
        'per_minggu' => 0,
        'per_hari' => 0,
    ];
    public ?string $genderFilter = null;
    public ?int $unitFilter = null;
    public array $yearOptions = [];

    public function mount(?string $genderFilter = null, ?int $unitFilter = null): void
    {
        $this->genderFilter = $genderFilter;
        $this->unitFilter = $unitFilter;
        $this->year = (int) now()->year;
        $this->yearOptions = range($this->year - 1, $this->year + 2);
        $this->loadSurahOptions();
    }

    public function updatedJuz($value): void
    {
        $this->juz = $value ? (int) $value : null;
        $this->loadSurahOptions();
        $this->surahStartId = null;
        $this->surahEndId = null;
        $this->ayatStart = null;
        $this->ayatEnd = null;
        $this->summary = $this->emptySummary();
    }

    public function updatedSurahStartId(): void
    {
        $this->assignDefaultAyatForSurah('start');
        $this->ensureSurahOrder();
        $this->refreshSummary();
    }

    public function updatedSurahEndId(): void
    {
        $this->assignDefaultAyatForSurah('end');
        $this->ensureSurahOrder();
        $this->refreshSummary();
    }

    public function updatedAyatStart(): void
    {
        $this->clampAyatBounds();
        $this->refreshSummary();
    }

    public function updatedAyatEnd(): void
    {
        $this->clampAyatBounds();
        $this->refreshSummary();
    }

    public function saveTarget(): void
    {
        $this->validate([
            'santriId' => ['required', 'integer', Rule::exists('santri', 'id')],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'juz' => ['required', 'integer', 'between:1,30'],
            'surahStartId' => ['required', 'integer', 'between:1,114'],
            'surahEndId' => ['required', 'integer', 'between:1,114'],
            'ayatStart' => ['required', 'integer', 'min:1'],
            'ayatEnd' => ['required', 'integer', 'min:1'],
        ], [], [
            'santriId' => 'santri',
            'year' => 'tahun',
            'juz' => 'juz',
            'surahStartId' => 'surat awal',
            'surahEndId' => 'surat akhir',
            'ayatStart' => 'ayat awal',
            'ayatEnd' => 'ayat akhir',
        ]);

        if (! $this->ensureSantriWithinScope((int) $this->santriId)) {
            $this->addError('santriId', 'Santri ini tidak berada di unit / gender yang Anda kelola.');
            return;
        }

        if ($this->surahEndId < $this->surahStartId) {
            $this->addError('surahEndId', 'Surat akhir harus berada setelah surat awal.');
            return;
        }

        if ($this->surahStartId === $this->surahEndId && $this->ayatEnd < $this->ayatStart) {
            $this->addError('ayatEnd', 'Ayat akhir tidak boleh lebih kecil dari ayat awal.');
            return;
        }

        $totalAyat = $this->calculateTotalAyat();
        if ($totalAyat <= 0) {
            $this->addError('ayatEnd', 'Rentang surat / ayat tidak valid untuk Juz yang dipilih.');
            return;
        }

        $perBulan = (int) max(1, ceil($totalAyat / 12));
        $perMinggu = (int) max(1, ceil($totalAyat / 52));
        $perHari = (int) max(1, ceil($totalAyat / 365));

        HafalanTarget::updateOrCreate(
            [
                'santri_id' => $this->santriId,
                'tahun' => $this->year,
            ],
            [
                'created_by' => Auth::id(),
                'juz' => $this->juz,
                'surah_start_id' => $this->surahStartId,
                'surah_end_id' => $this->surahEndId,
                'ayat_start' => $this->ayatStart,
                'ayat_end' => $this->ayatEnd,
                'total_ayat' => $totalAyat,
                'target_per_bulan' => $perBulan,
                'target_per_minggu' => $perMinggu,
                'target_per_hari' => $perHari,
            ]
        );

        $this->summary = [
            'total_ayat' => $totalAyat,
            'per_bulan' => $perBulan,
            'per_minggu' => $perMinggu,
            'per_hari' => $perHari,
        ];

        session()->flash('success', 'Target hafalan berhasil disimpan.');
        $this->dispatch('target-saved');
    }

    public function render()
    {
        // Pastikan opsi surat selalu sinkron dengan juz terpilih
        $this->surahOptions = $this->querySurahOptions();

        $santriOptions = $this->santriOptions;
        if (! $this->santriId && $santriOptions->isNotEmpty()) {
            $this->santriId = $santriOptions->first()->id;
        }

        $targets = HafalanTarget::with([
                'santri:id,nama,jenis_kelamin,unit_id',
                'santri.unit:id,nama_unit',
                'creator:id,name',
                'surahStart:id,nama_surah',
                'surahEnd:id,nama_surah',
            ])
            ->whereHas('santri', function ($query) {
                $query->when($this->genderFilter, fn ($q) => $q->where('jenis_kelamin', $this->genderFilter))
                      ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter));
            })
            ->orderByDesc('tahun')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tahfizh-target-planner', [
            'santriOptions' => $santriOptions,
            'surahOptions' => $this->surahOptions,
            'targets' => $targets,
        ]);
    }

    public function getSantriOptionsProperty(): Collection
    {
        return Santri::query()
            ->select('id', 'nama', 'jenis_kelamin', 'unit_id')
            ->when($this->genderFilter, fn ($q) => $q->where('jenis_kelamin', $this->genderFilter))
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->orderBy('nama')
            ->get();
    }

    protected function loadSurahOptions(): void
    {
        $this->surahOptions = $this->querySurahOptions();
        $this->dispatch('planner-surah-options', options: $this->surahOptions);
    }

    protected function querySurahOptions(): array
    {
        if (! $this->juz) {
            return [];
        }
        $controller = app(SetoranHafalanController::class);
        $rows = $controller->getSuratByJuz((int) $this->juz);
        $collection = $rows instanceof \Illuminate\Support\Collection ? $rows : collect($rows);
        $result = $collection
            ->map(function ($row) {
                $surahId = data_get($row, 'surah_id');
                $nama = data_get($row, 'nama_latin') ?? data_get($row, 'nama_surah');
                $ayatAwal = (int) (data_get($row, 'ayat_awal') ?? 1);
                $ayatAkhir = (int) (data_get($row, 'ayat_akhir') ?? data_get($row, 'jumlah_ayat', $ayatAwal));

                return [
                    'id' => (int) $surahId,
                    'name' => $nama,
                    'min' => $ayatAwal,
                    'max' => $ayatAkhir,
                ];
            })
            ->filter(fn ($item) => $item['id'] > 0 && !blank($item['name']))
            ->values()
            ->all();
        return $result;
    }

    protected function ensureSantriWithinScope(int $santriId): bool
    {
        return Santri::query()
            ->when($this->genderFilter, fn ($q) => $q->where('jenis_kelamin', $this->genderFilter))
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->where('id', $santriId)
            ->exists();
    }

    protected function assignDefaultAyatForSurah(string $position): void
    {
        $surahId = $position === 'start' ? $this->surahStartId : $this->surahEndId;
        if (! $surahId) {
            return;
        }

        $meta = $this->findSurahMeta($surahId);
        if (! $meta) {
            return;
        }

        if ($position === 'start') {
            $this->ayatStart = $meta['min'];
        } else {
            $this->ayatEnd = $meta['max'];
        }
    }

    protected function ensureSurahOrder(): void
    {
        if ($this->surahStartId && $this->surahEndId && $this->surahEndId < $this->surahStartId) {
            $this->surahEndId = $this->surahStartId;
        }
        $this->clampAyatBounds();
    }

    protected function clampAyatBounds(): void
    {
        if ($this->surahStartId) {
            if ($meta = $this->findSurahMeta($this->surahStartId)) {
                $this->ayatStart = $this->normalizeAyat($this->ayatStart, $meta['min'], $meta['max'], $meta['min']);
            }
        }

        if ($this->surahEndId) {
            if ($meta = $this->findSurahMeta($this->surahEndId)) {
                $this->ayatEnd = $this->normalizeAyat($this->ayatEnd, $meta['min'], $meta['max'], $meta['max']);
            }
        }

        if ($this->surahStartId === $this->surahEndId && $this->ayatStart !== null && $this->ayatEnd !== null && $this->ayatEnd < $this->ayatStart) {
            $this->ayatEnd = $this->ayatStart;
        }
    }

    protected function refreshSummary(): void
    {
        $total = $this->calculateTotalAyat();
        if ($total <= 0) {
            $this->summary = $this->emptySummary();
            return;
        }

        $this->summary = [
            'total_ayat' => $total,
            'per_bulan' => (int) max(1, ceil($total / 12)),
            'per_minggu' => (int) max(1, ceil($total / 52)),
            'per_hari' => (int) max(1, ceil($total / 365)),
        ];
    }

    protected function calculateTotalAyat(): int
    {
        if (! $this->juz || ! $this->surahStartId || ! $this->surahEndId || ! $this->ayatStart || ! $this->ayatEnd) {
            return 0;
        }

        if ($this->surahEndId < $this->surahStartId) {
            return 0;
        }

        $segments = DB::table('quran_juz_map as jm')
            ->join('quran_surah as s', 's.id', '=', 'jm.surah_id')
            ->select('jm.surah_id', 'jm.ayat_awal', 'jm.ayat_akhir', 's.jumlah_ayat')
            ->where('jm.juz', $this->juz)
            ->whereBetween('jm.surah_id', [$this->surahStartId, $this->surahEndId])
            ->orderBy('jm.surah_id')
            ->orderBy('jm.ayat_awal')
            ->get();

        if ($segments->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($segments as $segment) {
            $segStart = $segment->ayat_awal ?: 1;
            $segEnd = $segment->ayat_akhir ?: $segment->jumlah_ayat;

            if ($segment->surah_id == $this->surahStartId) {
                $segStart = max($segStart, $this->ayatStart);
            }

            if ($segment->surah_id == $this->surahEndId) {
                $segEnd = min($segEnd, $this->ayatEnd);
            }

            if ($segEnd < $segStart) {
                continue;
            }

            $total += ($segEnd - $segStart + 1);
        }

        return (int) $total;
    }

    protected function findSurahMeta(?int $surahId): ?array
    {
        if (! $surahId) {
            return null;
        }

        return collect($this->surahOptions)->firstWhere('id', $surahId);
    }

    protected function normalizeAyat(?int $value, int $min, int $max, int $fallback): int
    {
        $value = $value ?? $fallback;
        $value = max($min, $value);
        $value = min($max, $value);
        return $value;
    }

    protected function emptySummary(): array
    {
        return [
            'total_ayat' => 0,
            'per_bulan' => 0,
            'per_minggu' => 0,
            'per_hari' => 0,
        ];
    }
}
