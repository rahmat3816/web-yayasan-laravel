<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
                ? Halaqoh::with(['santri:id,nama,jenis_kelamin'])->findOrFail($halaqohId)
                : Halaqoh::with(['santri:id,nama,jenis_kelamin'])->first();

            $allHalaqoh = Halaqoh::with('guru:id,nama')
                ->orderBy('id')
                ->get(['id', 'unit_id', 'guru_id']);
        } else {
            $linkedGuruId = $user->ensureLinkedGuruId();
            if (!$linkedGuruId) {
                return redirect()->route('dashboard')
                    ->with('error', 'Akun Anda belum ditautkan sebagai guru pengampu.');
            }
            $halaqoh = Halaqoh::with(['santri:id,nama,jenis_kelamin'])
                ->where('guru_id', $linkedGuruId)
                ->firstOrFail();
            $allHalaqoh = collect();
        }

        // ===============================
        // 🧮 Rekap Data Hafalan
        // ===============================
        $dataAll = HafalanQuran::where('halaqoh_id', $halaqoh->id)->get();
        $rekap = $this->hitungRekapHafalan($dataAll);

        if ($dataAll->count() > 0) {
            $rekap['total_halaman'] = $dataAll->sum(function ($h) {
                if ($h->mode === 'page' && $h->page_start && $h->page_end) {
                    return ($h->page_end - $h->page_start) + 1;
                }
                return 0;
            });

            $rekap['total_juz'] = round(($rekap['total_halaman'] ?? 0) / 20, 2); // ✅ tambahkan proporsional
            $rekap['total_surah'] = $dataAll->whereNotNull('surah_id')->unique('surah_id')->count();
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
    // ✍️ Form tambah hafalan
    // ===============================
    public function create(int $santriId)
    {
        $user = Auth::user();
        $linkedGuruId = $user->ensureLinkedGuruId();
        if (!$linkedGuruId) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akun Anda belum ditautkan ke data guru pengampu.');
        }

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
        $linkedGuruId = $user->ensureLinkedGuruId();
        if (!$linkedGuruId) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akun Anda belum ditautkan ke data guru pengampu.');
        }
        $halaqoh = Halaqoh::where('guru_id', $linkedGuruId)->firstOrFail();
        $santri  = $halaqoh->santri()->where('santri.id', $santriId)->firstOrFail();

        $data = $request->validate([
            'tanggal_setor'    => ['required', 'date'],
            'penilaian_tajwid' => ['required', 'integer', 'between:1,5'],
            'penilaian_mutqin' => ['required', 'integer', 'between:1,10'],
            'penilaian_adab'   => ['required', 'integer', 'between:1,5'],
            'catatan'          => ['nullable', 'string'],
            'juz_start'        => ['required', 'integer', 'between:1,30'],
            'surah_id'         => ['required', 'integer', 'between:1,114'],
            'ayah_start'       => ['required', 'integer', 'min:1'],
            'ayah_end'         => ['required', 'integer', 'gte:ayah_start'],
        ]);

        // 🧠 Validasi progres dan overlap
        $lastOverall = HafalanQuran::where('santri_id', $santriId)
            ->where('juz_start', $data['juz_start'])
            ->orderByDesc('surah_id')
            ->orderByDesc('ayah_end')
            ->first();

        // if ($lastOverall && (int)$data['surah_id'] < (int)$lastOverall->surah_id) {
        //     return back()->withErrors([
        //         'surah_id' => "Urutan surah tidak boleh mundur. Terakhir disetor: Surah {$lastOverall->surah_id}."
        //     ])->withInput();
        // }

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

        // Simpan data
        $status = $this->deriveStatusFromPenilaian(
            (int) $data['penilaian_tajwid'],
            (int) $data['penilaian_mutqin'],
            (int) $data['penilaian_adab']
        );

        HafalanQuran::create([
            'unit_id'            => $halaqoh->unit_id,
            'halaqoh_id'         => $halaqoh->id,
            'guru_id'            => $linkedGuruId,
            'santri_id'          => $santri->id,
            'tanggal_setor'      => $data['tanggal_setor'],
            'mode'               => 'ayat',
            'surah_id'           => $data['surah_id'],
            'ayah_start'         => $data['ayah_start'],
            'ayah_end'           => $data['ayah_end'],
            'juz_start'          => $data['juz_start'],
            'juz_end'            => $data['juz_start'],
            'status'             => $status,
            'penilaian_tajwid'   => $data['penilaian_tajwid'],
            'penilaian_mutqin'   => $data['penilaian_mutqin'],
            'penilaian_adab'     => $data['penilaian_adab'],
            'catatan'            => $data['catatan'] ?? null,
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
        if ($user->role === 'superadmin') {
            $halaqoh = Halaqoh::findOrFail((int) $request->query('halaqoh_id', 1));
        } else {
            $linkedGuruId = $user->ensureLinkedGuruId();
            if (!$linkedGuruId) {
                return redirect()->route('guru.dashboard')
                    ->with('error', 'Akun Anda belum ditautkan ke data guru pengampu.');
            }
            $halaqoh = Halaqoh::where('guru_id', $linkedGuruId)->firstOrFail();
        }

        $query = HafalanQuran::where('halaqoh_id', $halaqoh->id)
            ->with(['santri:id,nama'])
            ->orderByDesc('tanggal_setor');

        $rekap = $this->hitungRekapHafalan((clone $query)->get());
        $data = $query->paginate(10)->withQueryString();

        return view('guru.setoran.rekap', compact('data', 'rekap', 'halaqoh'));
    }

    // ===============================
    // 🔄 AJAX
    // ===============================
    public function getSetoranSantri(int $santriId)
    {
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
     * 🔎 Ambil daftar surah berdasarkan Juz langsung dari database
     */
    public function getSuratByJuz(int $juz)
    {
        $signature = Cache::remember('quran.juz_map.signature', now()->addMinutes(5), function () {
            $latest = DB::table('quran_juz_map')->max('updated_at');
            if ($latest) {
                return Carbon::parse($latest)->format('YmdHis');
            }
            return (string) DB::table('quran_juz_map')->max('id');
        });

        $cacheKey = "quran.juz.$juz.surah.$signature";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($juz) {
            $rows = DB::table('quran_juz_map as jm')
                ->join('quran_surah as s', 's.id', '=', 'jm.surah_id')
                ->where('jm.juz', $juz)
                ->orderBy('jm.surah_id')
                ->select([
                    'jm.surah_id',
                    's.nama_surah as nama_latin',
                    's.jumlah_ayat',
                    'jm.ayat_awal as ayat_awal',
                    'jm.ayat_akhir as ayat_akhir',
                ])
                ->get();

            return $rows->map(fn ($r) => [
                'surah_id'    => $r->surah_id,
                'nama_latin'  => $r->nama_latin,
                'jumlah_ayat' => $r->jumlah_ayat,
                'ayat_awal'   => $r->ayat_awal,
                'ayat_akhir'  => $r->ayat_akhir,
            ])->values();
        });
    }

    // ===============================
    // 🧮 Hitung rekap hafalan
    // ===============================
    private function hitungRekapHafalan($hafalan): array
    {
        $pageMap = Cache::rememberForever('quran_page_map_all', fn() =>
            DB::table('quran_page_map')->select('page', 'juz', 'surah_id', 'ayat_awal', 'ayat_akhir')->get()
        );

        $juzMap = Cache::rememberForever('quran_juz_map_all', fn() =>
            DB::table('quran_juz_map')->get()->groupBy('juz')
        );

        $halamanSetor = collect();
        $ayatDisetor = [];

        foreach ($hafalan as $h) {
            if (!$h->surah_id || !$h->ayah_start || !$h->ayah_end) continue;

            for ($a = $h->ayah_start; $a <= $h->ayah_end; $a++) {
                $ayatDisetor[$h->surah_id][] = $a;
                foreach ($pageMap as $p) {
                    if ($p->surah_id == $h->surah_id && $a >= $p->ayat_awal && $a <= $p->ayat_akhir) {
                        $halamanSetor->push(['page' => $p->page, 'juz' => $p->juz]);
                        break;
                    }
                }
            }
        }

        $totalHalaman = $halamanSetor->pluck('page')->unique()->count();
        $totalAyatDisetor = 0;
        $totalAyatSurah = 0;
        $jumlahSurahTersentuh = count($ayatDisetor);

        foreach ($ayatDisetor as $surahId => $ayats) {
            $jumlahAyatSurah = Cache::rememberForever("quran_surah_$surahId", fn() =>
                DB::table('quran_surah')->where('id', $surahId)->value('jumlah_ayat')
            );
            $ayatSetoranUnik = count(array_unique($ayats));
            $totalAyatDisetor += $ayatSetoranUnik;
            $totalAyatSurah += $jumlahAyatSurah;
        }

        $progressSurah = $totalAyatSurah > 0 ? round(($totalAyatDisetor / $totalAyatSurah) * 100, 2) : 0;
        $progressJuzPersen = 0;

        foreach ($juzMap as $juz => $entries) {
            $halamanDalamJuz = $pageMap->where('juz', (int)$juz)->pluck('page')->unique();
            $halamanSetoranJuz = $halamanSetor->where('juz', (int)$juz)->pluck('page')->unique();
            $progress = $halamanDalamJuz->count() > 0 ? min(100, round(($halamanSetoranJuz->count() / 20) * 100, 2)) : 0;
            $progressJuzPersen += $progress;
        }

        $rataRataJuz = $juzMap->count() > 0 ? round($progressJuzPersen / $juzMap->count(), 2) : 0;

        return [
            'total_halaman'      => $totalHalaman,
            'total_juz'          => round($totalHalaman / 20, 2),
            'total_surah'        => $jumlahSurahTersentuh,
            'progress_surah'     => $progressSurah,
            'progress_juz'       => $rataRataJuz,
            'total_ayat_disetor' => $totalAyatDisetor,
            'total_ayat_target'  => $totalAyatSurah,
        ];
    }

    /**
     * 🚀 Refresh cache Quran (opsional)
     */
    private function refreshQuranCache(): void
    {
        Cache::forget('quran_page_map_all');
        Cache::forget('quran_juz_map_all');

        $allSurah = DB::table('quran_surah')->pluck('id');
        foreach ($allSurah as $sid) {
            Cache::forget("quran_surah_$sid");
        }

        Cache::rememberForever('quran_page_map_all', fn() =>
            DB::table('quran_page_map')->select('page', 'juz', 'surah_id', 'ayat_awal', 'ayat_akhir')->get()
        );
        Cache::rememberForever('quran_juz_map_all', fn() =>
            DB::table('quran_juz_map')->get()->groupBy('juz')
        );
    }

    protected function deriveStatusFromPenilaian(int $tajwid, int $mutqin, int $adab): string
    {
        if ($tajwid >= 3 && $adab >= 3 && $mutqin >= 7) {
            return 'lulus';
        }

        return 'ulang';
    }

}
