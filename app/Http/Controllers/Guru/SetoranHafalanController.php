<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Halaqoh;
use App\Models\Santri;
use App\Models\HafalanQuran;
use App\Models\QuranJuzMap;
use App\Models\QuranSurah;
use App\Rules\NoOverlapHafalan;

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

        $rekap = [
            'total_halaman' => HafalanQuran::where('halaqoh_id', $halaqoh->id)
                ->sum(fn($h) => $h->mode === 'page' && $h->page_start && $h->page_end ? (($h->page_end - $h->page_start) + 1) : 0),
            'total_juz' => HafalanQuran::where('halaqoh_id', $halaqoh->id)
                ->whereNotNull('juz_start')
                ->whereNotNull('juz_end')
                ->get()
                ->flatMap(fn($h) => range($h->juz_start, $h->juz_end))
                ->unique()
                ->count(),
            'total_surah' => HafalanQuran::where('halaqoh_id', $halaqoh->id)
                ->whereNotNull('surah_id')
                ->distinct('surah_id')
                ->count('surah_id'),
        ];

        return view('guru.setoran.index', [
            'halaqoh'    => $halaqoh,
            'allHalaqoh' => $allHalaqoh,
            'santri'     => $halaqoh?->santri ?? collect(),
            'rekap'      => $rekap,
            'isSuper'    => $user->role === 'superadmin',
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
            'mode'          => ['required', Rule::in(['ayat', 'page'])],
            'status'        => ['nullable', Rule::in(['lulus', 'ulang'])],
            'catatan'       => ['nullable', 'string'],
            'juz_start'     => ['nullable', 'integer', 'between:1,30'],
            'surah_id'      => ['nullable', 'integer', 'between:1,114'],
            'ayah_start'    => ['nullable', 'integer', 'min:1'],
            'ayah_end'      => ['nullable', 'integer', 'gte:ayah_start'],
            'page_start'    => ['nullable', 'integer', 'min:1', 'max:604'],
            'page_end'      => ['nullable', 'integer', 'min:1', 'max:604', 'gte:page_start'],
        ]);

        // 🧠 Validasi overlap hafalan (mode ayat)
        if ($data['mode'] === 'ayat' && !empty($data['juz_start']) && !empty($data['surah_id'])) {
            $last = HafalanQuran::where('santri_id', $santriId)
                ->where('juz_start', $data['juz_start'])
                ->orderByDesc('surah_id')
                ->orderByDesc('ayah_end')
                ->first();

            if ($last && (
                $data['surah_id'] < $last->surah_id ||
                ($data['surah_id'] == $last->surah_id && $data['ayah_start'] <= $last->ayah_end)
            )) {
                return back()->withErrors([
                    'ayah_start' => "Setoran baru tumpang tindih dengan setoran sebelumnya (Surah {$last->surah_id}, Ayat {$last->ayah_end})."
                ])->withInput();
            }
        }

        // 🧾 Simpan data hafalan
        HafalanQuran::create([
            'unit_id'       => $halaqoh->unit_id,
            'halaqoh_id'    => $halaqoh->id,
            'guru_id'       => $linkedGuruId,
            'santri_id'     => $santri->id,
            'tanggal_setor' => $data['tanggal_setor'],
            'mode'          => $data['mode'],
            'page_start'    => $data['page_start'] ?? null,
            'page_end'      => $data['page_end'] ?? null,
            'surah_id'      => $data['surah_id'] ?? null,
            'ayah_start'    => $data['ayah_start'] ?? null,
            'ayah_end'      => $data['ayah_end'] ?? null,
            'juz_start'     => $data['juz_start'] ?? null,
            'juz_end'       => $data['juz_start'] ?? null,
            'status'        => $data['status'] ?? 'lulus',
            'catatan'       => $data['catatan'] ?? null,
        ]);

        return redirect()
            ->route('guru.setoran.index')
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
            'total_juz'   => $data->flatMap(fn($h) => range($h->juz_start ?? 0, $h->juz_end ?? 0))->unique()->count(),
            'total_surah' => $data->unique('surah_id')->count(),
        ];

        return view('guru.setoran.rekap', compact('data', 'rekap', 'halaqoh'));
    }

    // ===============================
    // 🔄 AJAX
    // ===============================
    public function getSetoranSantri(int $santriId)
    {
        $data = HafalanQuran::where('santri_id', $santriId)
            ->select('juz_start as juz', 'surah_id as surat_akhir', 'ayah_end as ayat_akhir')
            ->orderBy('juz_start')
            ->orderBy('surah_id')
            ->orderBy('ayah_end')
            ->get();

        return response()->json($data);
    }

    public function getSuratByJuz(int $juz)
    {
        $maps = QuranJuzMap::where('juz', $juz)
            ->join('quran_surah', 'quran_surah.id', '=', 'quran_juz_map.surah_id')
            ->select('quran_juz_map.surah_id', 'quran_juz_map.ayat_awal', 'quran_juz_map.ayat_akhir', 'quran_surah.nama_latin')
            ->orderBy('quran_juz_map.surah_id')
            ->get();

        return response()->json($maps);
    }
}
