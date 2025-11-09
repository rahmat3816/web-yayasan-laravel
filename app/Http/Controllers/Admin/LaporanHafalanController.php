<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\HafalanQuran;
use App\Models\Halaqoh;

class LaporanHafalanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int)($request->query('tahun', now()->year));
        $bulan = (int)($request->query('bulan', now()->month));
        $unitId = $request->query('unit_id');
        $guruId = $request->query('guru_id');
        $santriId = $request->query('santri_id');

        $query = HafalanQuran::query()
            ->with(['santri:id,nama,unit_id', 'guru:id,nama', 'halaqoh:id,nama_halaqoh'])
            ->whereYear('tanggal_setor', $tahun)
            ->whereMonth('tanggal_setor', $bulan);

        if ($unitId) {
            $query->whereHas('santri', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }
        if ($guruId) {
            $query->where('guru_id', $guruId);
        }
        if ($santriId) {
            $query->where('santri_id', $santriId);
        }

        $data = $query->orderByDesc('tanggal_setor')->get();

        // 🔹 Rekap proporsional (halaman, surah %, juz %)
        $rekap = $this->hitungRekapHafalan($data);

        // 🔹 Grafik per hari
        $grafikPerHari = $data->groupBy(fn($r) => Carbon::parse($r->tanggal_setor)->format('d M'))
            ->map->count();

        // 🔹 Grafik kumulatif ayat
        $totalAyatPerHari = [];
        $totalAyat = 0;

        foreach ($data->sortBy('tanggal_setor') as $h) {
            $tanggal = Carbon::parse($h->tanggal_setor)->format('d M');
            $jumlahAyat = max(0, ($h->ayah_end - $h->ayah_start + 1));
            $totalAyat += $jumlahAyat;
            $totalAyatPerHari[$tanggal] = $totalAyat;
        }

        // 🔹 Rekap per guru
        $rekapGuru = $data->groupBy('guru_id')->map(fn($g) => [
            'nama' => optional($g->first()->guru)->nama ?? '-',
            'jumlah_setoran' => $g->count(),
            'total_santri' => $g->unique('santri_id')->count(),
        ]);

        // 🔹 Grafik ayat kumulatif per santri
        $grafikSantri = $data->groupBy('santri_id')->mapWithKeys(function ($group, $santriId) {
            $nama = optional($group->first()->santri)->nama ?? "Santri ID $santriId";
            $totalAyat = $group->sum(function ($h) {
                return max(0, ($h->ayah_end - $h->ayah_start + 1));
            });
            return [$nama => $totalAyat];
        });

        // 🔹 Dropdown
        $units = Unit::orderBy('nama_unit')->get(['id', 'nama_unit']);
        $guruList = Guru::orderBy('nama')->get(['id', 'nama']);
        $santriList = Santri::orderBy('nama')->get(['id', 'nama']);

        return view('admin.laporan.hafalan', compact(
            'data', 'rekap', 'grafikPerHari',
            'rekapGuru', 'units', 'guruList', 'santriList',
            'tahun', 'bulan', 'unitId', 'guruId', 'santriId',
            'totalAyatPerHari', 'grafikSantri'
        ));
    }

    public function grafikSantri(int $id)
    {
        $data = HafalanQuran::where('santri_id', $id)
            ->orderBy('tanggal_setor')
            ->get(['tanggal_setor', 'juz_start', 'surah_id', 'ayah_start', 'ayah_end']);

        $grafik = [];
        $total = 0;

        foreach ($data as $h) {
            $jumlahAyat = max(0, ($h->ayah_end - $h->ayah_start) + 1);
            $total += $jumlahAyat;
            $grafik[] = [
                'tanggal' => \Carbon\Carbon::parse($h->tanggal_setor)->format('Y-m-d'),
                'total'   => $total,
            ];
        }

        return response()->json($grafik);
    }

    /**
     * 🧮 Hitung rekap hafalan (proporsional)
     * Menghitung total halaman, progress surah (%), progress juz (%)
     */
    private function hitungRekapHafalan($hafalan): array
    {
        // ⚡️ Tambahkan caching otomatis
        $pageMap = Cache::rememberForever('quran_page_map_all', function () {
            return DB::table('quran_page_map')
                ->select('page', 'juz', 'surah_id', 'ayat_awal', 'ayat_akhir')
                ->get();
        });

        $juzMap = Cache::rememberForever('quran_juz_map_all', function () {
            return DB::table('quran_juz_map')->get()->groupBy('juz');
        });

        // ✅ Ambil data langsung dari database, bukan dari JSON
        $halamanSetor = collect();
        $ayatDisetor = [];

        // 🔍 Kumpulkan semua ayat & halaman disetor
        foreach ($hafalan as $h) {
            if (!$h->surah_id || !$h->ayah_start || !$h->ayah_end) continue;

            for ($a = $h->ayah_start; $a <= $h->ayah_end; $a++) {
                $ayatDisetor[$h->surah_id][] = $a;

                foreach ($pageMap as $p) {
                    if (
                        $p->surah_id == $h->surah_id &&
                        $a >= $p->ayat_awal &&
                        $a <= $p->ayat_akhir
                    ) {
                        $halamanSetor->push([
                            'page' => $p->page,
                            'juz'  => $p->juz,
                        ]);
                        break;
                    }
                }
            }
        }

        // 📘 Total halaman unik
        $totalHalaman = $halamanSetor->pluck('page')->unique()->count();

        // 📗 Progress Surah (%)
        $totalAyatDisetor = 0;
        $totalAyatSurah   = 0;

        foreach ($ayatDisetor as $surahId => $ayats) {
            $jumlahAyatSurah = Cache::rememberForever("quran_surah_{$surahId}", function () use ($surahId) {
                return DB::table('quran_surah')->where('id', $surahId)->value('jumlah_ayat');
            });

            $ayatSetoranUnik = count(array_unique($ayats));

            $totalAyatDisetor += $ayatSetoranUnik;
            $totalAyatSurah   += $jumlahAyatSurah;
        }

        $progressSurah = $totalAyatSurah > 0
            ? round(($totalAyatDisetor / $totalAyatSurah) * 100, 2)
            : 0;

        // 📕 Progress Juz (%)
        $progressJuzPersen = 0;

        foreach ($juzMap as $juz => $entries) {
            $halamanDalamJuz = $pageMap
                ->where('juz', (int)$juz)
                ->pluck('page')
                ->unique()
                ->values();

            $halamanSetoranJuz = $halamanSetor
                ->where('juz', (int)$juz)
                ->pluck('page')
                ->unique();

            $progress = $halamanDalamJuz->count() > 0
                ? min(100, round(($halamanSetoranJuz->count() / 20) * 100, 2))
                : 0;

            $progressJuzPersen += $progress;
        }

        $rataRataJuz = $juzMap->count() > 0
            ? round($progressJuzPersen / $juzMap->count(), 2)
            : 0;

        // 🧾 Hasil akhir
        return [
            'total_halaman'      => $totalHalaman,
            'total_juz'          => round($totalHalaman / 20, 2),
            'progress_surah'     => $progressSurah,
            'progress_juz'       => $rataRataJuz,
            'total_ayat_disetor' => $totalAyatDisetor,
            'total_ayat_target'  => $totalAyatSurah,
        ];
    }

    /**
     * 🚀 Tambahan helper untuk refresh cache Quran
     */
    private function refreshQuranCache(): void
    {
        Cache::forget('quran_page_map_all');
        Cache::forget('quran_juz_map_all');

        $allSurah = DB::table('quran_surah')->pluck('id');
        foreach ($allSurah as $sid) {
            Cache::forget("quran_surah_{$sid}");
        }

        Cache::rememberForever('quran_page_map_all', fn() =>
            DB::table('quran_page_map')->select('page', 'juz', 'surah_id', 'ayat_awal', 'ayat_akhir')->get()
        );
        Cache::rememberForever('quran_juz_map_all', fn() =>
            DB::table('quran_juz_map')->get()->groupBy('juz')
        );
    }
}
