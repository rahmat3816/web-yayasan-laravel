<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Models\Halaqoh;
use App\Models\Santri;
use App\Models\HafalanQuran;

class SetoranHafalanController extends Controller
{
    // ===============================
    // 📋 Daftar santri + rekap ringkas
    // ===============================
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $halaqohId = (int) $request->query('halaqoh_id', 0);
            $halaqoh = $halaqohId
                ? Halaqoh::with(['santri:id,nama'])->findOrFail($halaqohId)
                : Halaqoh::with(['santri:id,nama'])->first();

            $allHalaqoh = Halaqoh::with('guru:id,nama')
                ->orderBy('id')
                ->get(['id', 'unit_id', 'guru_id']);
        } else {
            $linkedGuruId = (int) $user->linked_guru_id;
            $halaqoh = Halaqoh::with(['santri:id,nama'])
                ->where('guru_id', $linkedGuruId)
                ->firstOrFail();
            $allHalaqoh = collect();
        }

        // ===============================
        // 🧮 Rekap Data Hafalan
        // ===============================
        $dataAll = HafalanQuran::where('halaqoh_id', $halaqoh->id)->get();

        $rekap = [
            'total_halaman' => 0,
            'total_juz'     => 0,
            'total_surah'   => 0,
        ];

        if ($dataAll->count() > 0) {
            $rekap['total_halaman'] = $dataAll->sum(function ($h) {
                if ($h->mode === 'page' && $h->page_start && $h->page_end) {
                    return ($h->page_end - $h->page_start) + 1;
                }
                return 0;
            });

            $rekap['total_juz'] = $dataAll
                ->filter(fn($h) => $h->juz_start && $h->juz_end)
                ->flatMap(fn($h) => range($h->juz_start, $h->juz_end))
                ->unique()
                ->count();

            $rekap['total_surah'] = $dataAll
                ->whereNotNull('surah_id')
                ->unique('surah_id')
                ->count();
        }

        return view('guru.setoran.index', [
            'halaqoh'    => $halaqoh,
            'allHalaqoh' => $allHalaqoh,
            'santri'     => $halaqoh?->santri ?? collect(),
            'rekap'      => $rekap,
            'isSuper'    => strtolower($user->role ?? '') === 'superadmin',
        ]);
    }

    // ===============================
    // ✍️ Form tambah hafalan (guru pengampu)
    // ===============================
    public function create(int $santriId)
    {
        $user = Auth::user();
        $linkedGuruId = (int) $user->linked_guru_id;

        $halaqoh = Halaqoh::where('guru_id', $linkedGuruId)->firstOrFail();
        $santri  = $halaqoh->santri()->where('santri.id', $santriId)->firstOrFail();

        return view('guru.setoran.create', compact('halaqoh', 'santri'));
    }

    // ===============================
    // 💾 Simpan hafalan
    // ===============================
    public function store(Request $request, int $santriId)
    {
        $user = Auth::user();
        $linkedGuruId = (int) $user->linked_guru_id;
        $halaqoh = Halaqoh::where('guru_id', $linkedGuruId)->firstOrFail();
        $santri  = $halaqoh->santri()->where('santri.id', $santriId)->firstOrFail();

        $data = $request->validate([
            'tanggal_setor' => ['required', 'date'],
            'status'        => ['nullable', Rule::in(['lulus', 'ulang'])],
            'catatan'       => ['nullable', 'string'],
            'juz_start'     => ['required', 'integer', 'between:1,30'],
            'surah_id'      => ['required', 'integer', 'between:1,114'],
            'ayah_start'    => ['required', 'integer', 'min:1'],
            'ayah_end'      => ['required', 'integer', 'gte:ayah_start'],
        ]);

        // ===============================
        // 🧠 Validasi overlap & urutan progres per juz
        // ===============================

        // [NEW] Cek urutan surah tidak mundur relatif terhadap progres tertinggi di juz ini
        $lastOverall = HafalanQuran::where('santri_id', $santriId)
            ->where('juz_start', $data['juz_start'])
            ->orderByDesc('surah_id')
            ->orderByDesc('ayah_end')
            ->first();

        if ($lastOverall && (int)$data['surah_id'] < (int)$lastOverall->surah_id) {
            return back()->withErrors([
                'surah_id' => "Urutan surah tidak boleh mundur. Terakhir disetor: Surah {$lastOverall->surah_id}."
            ])->withInput();
        }

        // [UPDATED] Cek surah yang sama wajib mulai dari ayat (last_end + 1)
        $lastThisSurah = HafalanQuran::where('santri_id', $santriId)
            ->where('juz_start', $data['juz_start'])
            ->where('surah_id', $data['surah_id'])
            ->orderByDesc('ayah_end')
            ->first();

        if ($lastThisSurah) {
            $requiredNextStart = ((int)$lastThisSurah->ayah_end) + 1;
            if ((int)$data['ayah_start'] < $requiredNextStart) {
                return back()->withErrors([
                    'ayah_start' => "Setoran baru tumpang tindih atau mengulang (Surah {$lastThisSurah->surah_id}, Ayat {$lastThisSurah->ayah_end}). Mulai minimal dari ayat {$requiredNextStart}."
                ])->withInput();
            }
        }

        // 🔎 Validasi rentang ayat terhadap data_quran.json
        $ok = $this->validateRangeWithJson(
            (int) $data['juz_start'],
            (int) $data['surah_id'],
            (int) $data['ayah_start'],
            (int) $data['ayah_end']
        );
        if (!$ok) {
            return back()->withErrors([
                'ayah_start' => 'Rentang ayat tidak valid untuk pilihan Juz/Surah berdasarkan data_quran.json.'
            ])->withInput();
        }

        // 🧾 Simpan
        HafalanQuran::create([
            'unit_id'       => $halaqoh->unit_id,
            'halaqoh_id'    => $halaqoh->id,
            'guru_id'       => $linkedGuruId,
            'santri_id'     => $santri->id,
            'tanggal_setor' => $data['tanggal_setor'],
            'mode'          => 'ayat', // default mode
            'surah_id'      => $data['surah_id'],
            'ayah_start'    => $data['ayah_start'],
            'ayah_end'      => $data['ayah_end'],
            'juz_start'     => $data['juz_start'],
            'juz_end'       => $data['juz_start'],
            'status'        => $data['status'] ?? 'lulus',
            'catatan'       => $data['catatan'] ?? null,
        ]);

        return redirect()->route('guru.setoran.index')
            ->with('success', '✅ Setoran hafalan berhasil disimpan.');
    }

    // ===============================
    // 📊 Rekap detail
    // ===============================
    public function rekap(Request $request)
    {
        $user = Auth::user();
        $halaqoh = $user->role === 'superadmin'
            ? Halaqoh::findOrFail((int) $request->query('halaqoh_id', 1))
            : Halaqoh::where('guru_id', (int) $user->linked_guru_id)->firstOrFail();

        $data = HafalanQuran::where('halaqoh_id', $halaqoh->id)
            ->with(['santri:id,nama'])
            ->orderByDesc('tanggal_setor')
            ->get();

        $rekap = [
            'total_halaman' => $data->sum(function ($h) {
                if ($h->mode === 'page' && $h->page_start && $h->page_end) {
                    return ($h->page_end - $h->page_start) + 1;
                }
                return 0;
            }),
            'total_juz'   => $data->flatMap(fn($h) => range($h->juz_start ?? 0, $h->juz_end ?? 0))->unique()->count(),
            'total_surah' => $data->whereNotNull('surah_id')->unique('surah_id')->count(),
        ];

        return view('guru.setoran.rekap', compact('data', 'rekap', 'halaqoh'));
    }

    // ===============================
    // 🔄 AJAX
    // ===============================
    public function getSetoranSantri(int $santriId)
    {
        // [UPDATED] Ringkasan per (juz, surah) → ambil MAX(ayat_akhir)
        $data = HafalanQuran::query()
            ->where('santri_id', $santriId)
            ->selectRaw('juz_start as juz, surah_id as surat_akhir, MAX(ayah_end) as ayat_akhir')
            ->groupBy('juz_start', 'surah_id')
            ->orderBy('juz_start')
            ->orderBy('surah_id')
            ->get();

        return response()->json($data);
    }

    /**
     * 🔎 Ambil daftar surah (dan range ayat) untuk sebuah Juz dari data_quran.json
     */
    public function getSuratByJuz(int $juz)
    {
        $juz = (int) $juz;
        if ($juz < 1 || $juz > 30) {
            return response()->json([], 400);
        }

        $map = $this->loadQuranJson();
        $rows = $map[(string)$juz] ?? $map[$juz] ?? [];

        $result = [];
        foreach ($rows as $row) {
            if (isset($row['surat'])) {
                $clean = trim($row['surat']);
                if (preg_match('/^(\d{3})\.\s*(.+)$/u', $clean, $m)) {
                    $surahId = (int) ltrim($m[1], '0');
                    $nama    = trim($m[2]);
                    $result[] = [
                        'surah_id'    => $surahId,
                        'nama_latin'  => $nama,
                        'jumlah_ayat' => (int) ($row['jumlah_ayat'] ?? 0),
                        'ayat_awal'   => (int) ($row['ayat_mulai'] ?? 1),
                        'ayat_akhir'  => (int) ($row['ayat_akhir'] ?? ($row['jumlah_ayat'] ?? 0)),
                    ];
                }
            }
        }

        return response()->json($result);
    }

    // ===============================
    // 🔧 Helpers (load & validasi JSON)
    // ===============================
    private function loadQuranJson(): array
    {
        return Cache::rememberForever('quran.data_quran.json', function () {
            $candidates = [
                storage_path('app/data/data_quran.json'),
                storage_path('app/quran/data_quran.json'),
                resource_path('data/data_quran.json'),
                base_path('data_quran.json'),
            ];
            foreach ($candidates as $path) {
                if (is_file($path)) {
                    $json = @file_get_contents($path);
                    $data = json_decode($json, true);
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
            return [];
        });
    }

    /**
     * Validasi bahwa (juz, surah_id, ayat_start..ayat_end) sesuai dengan data JSON.
     */
    private function validateRangeWithJson(int $juz, int $surahId, int $ayahStart, int $ayahEnd): bool
    {
        $map = $this->loadQuranJson();
        $rows = $map[(string)$juz] ?? $map[$juz] ?? [];
        foreach ($rows as $row) {
            if (isset($row['surat']) && preg_match('/^\s*(\d{3})\./', $row['surat'], $m)) {
                $sid = (int) ltrim($m[1], '0');
                if ($sid === $surahId) {
                    $awal  = (int) ($row['ayat_mulai'] ?? 1);
                    $akhir = (int) ($row['ayat_akhir'] ?? ($row['jumlah_ayat'] ?? 0));
                    return $ayahStart >= $awal && $ayahEnd <= $akhir;
                }
            }
        }
        return false;
    }
}
