<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class GuruDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $guruId = $user->linked_guru_id;

        // 🗓️ Filter bulan & tahun
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));

        // 🚫 Jika akun belum terhubung dengan guru
        if (!$guruId) {
            return view('guru.dashboard', [
                'errorMessage'     => 'Akun ini belum terhubung ke data guru. Silakan hubungi admin unit.',
                'totalSantri'      => 0,
                'totalHafalan'     => 0,
                'hafalanPerMinggu' => collect(),
                'santriPerUnit'    => collect(),
                'daftarSantri'     => collect(),
                'rekapPerJuz'      => collect(),
                'rekapSurat'       => collect(),
                'totalBelumMulai'  => 0,
                'totalBerjalan'    => 0,
                'totalSelesai'     => 0,
                'targetBulanan'    => 0,
                'progressPersen'   => 0,
                'bulan'            => $bulan,
                'tahun'            => $tahun,
            ]);
        }

        // ===============================
        // 📊 Total Santri
        // ===============================
        $totalSantri = DB::table('halaqoh_santri as hs')
            ->join('halaqoh as h', 'h.id', '=', 'hs.halaqoh_id')
            ->where('h.guru_id', $guruId)
            ->distinct('hs.santri_id')
            ->count('hs.santri_id');

        // ===============================
        // 📖 Total Hafalan (bulan berjalan)
        // ===============================
        $totalHafalan = DB::table('hafalan_quran as hq')
            ->join('halaqoh as h', 'h.id', '=', 'hq.halaqoh_id')
            ->where('h.guru_id', $guruId)
            ->whereMonth('hq.tanggal_setor', $bulan)
            ->whereYear('hq.tanggal_setor', $tahun)
            ->count('hq.id');

        // 🎯 Target Hafalan Bulanan (sementara fixed)
        $targetBulanan  = 100; // TODO: ambil dari tabel target_hafalan jika sudah ada
        $progressPersen = $targetBulanan > 0 ? min(100, round(($totalHafalan / $targetBulanan) * 100, 2)) : 0;

        // ===============================
        // 📅 Hafalan Per Minggu
        // ===============================
        $hafalanPerMinggu = DB::table('hafalan_quran as hq')
            ->join('halaqoh as h', 'h.id', '=', 'hq.halaqoh_id')
            ->where('h.guru_id', $guruId)
            ->whereMonth('hq.tanggal_setor', $bulan)
            ->whereYear('hq.tanggal_setor', $tahun)
            ->selectRaw('WEEK(hq.tanggal_setor) as minggu, COUNT(hq.id) as total')
            ->groupBy('minggu')
            ->orderBy('minggu')
            ->pluck('total', 'minggu');

        // ===============================
        // 🏫 Santri per Unit (pakai `units.nama_unit`)
        // ===============================
        $santriPerUnit = DB::table('halaqoh_santri as hs')
            ->join('halaqoh as h', 'h.id', '=', 'hs.halaqoh_id')
            ->join('santri as s', 's.id', '=', 'hs.santri_id')
            ->leftJoin('units as u', 'u.id', '=', 's.unit_id')
            ->where('h.guru_id', $guruId)
            ->selectRaw('COALESCE(u.nama_unit, "(Tanpa Unit)") as unit, COUNT(s.id) as total')
            ->groupBy('unit')
            ->orderBy('unit')
            ->pluck('total', 'unit');

        // ===============================
        // 🧾 Daftar Santri Detail
        // ===============================
        $daftarSantri = DB::table('halaqoh_santri as hs')
            ->join('halaqoh as h', 'h.id', '=', 'hs.halaqoh_id')
            ->join('santri as s', 's.id', '=', 'hs.santri_id')
            ->leftJoin('units as u', 'u.id', '=', 's.unit_id')
            ->leftJoin('hafalan_quran as hq', function ($join) use ($bulan, $tahun) {
                $join->on('hq.santri_id', '=', 's.id')
                     ->whereMonth('hq.tanggal_setor', $bulan)
                     ->whereYear('hq.tanggal_setor', $tahun);
            })
            ->where('h.guru_id', $guruId)
            ->select(
                's.id',
                's.nama as nama_santri',
                DB::raw('COALESCE(u.nama_unit, "(Tanpa Unit)") as unit'),
                DB::raw('COUNT(hq.id) as total_hafalan'),
                DB::raw('MAX(hq.tanggal_setor) as terakhir_setor')
            )
            ->groupBy('s.id', 's.nama', 'unit')
            ->orderBy('s.nama')
            ->get();

        // ===============================
        // 🧮 Rekapan Progres Hafalan (tanpa kolom `mode`)
        //   - Jika ada kolom page_start/page_end → hitung total halaman.
        //   - Jika tidak ada → fallback jumlah entri (≥1 = berjalan, 0 = belum).
        // ===============================
        $hasPageStart = Schema::hasColumn('hafalan_quran', 'page_start');
        $hasPageEnd   = Schema::hasColumn('hafalan_quran', 'page_end');

        if ($hasPageStart && $hasPageEnd) {
            // Hitung total halaman per santri
            $progress = DB::table('santri as s')
                ->join('halaqoh_santri as hs', 'hs.santri_id', '=', 's.id')
                ->join('halaqoh as h', 'h.id', '=', 'hs.halaqoh_id')
                ->leftJoin('hafalan_quran as hq', 'hq.santri_id', '=', 's.id')
                ->where('h.guru_id', $guruId)
                ->select(
                    's.id',
                    DB::raw("(SUM(CASE
                                 WHEN hq.page_start IS NOT NULL
                                   THEN (COALESCE(hq.page_end, hq.page_start) - hq.page_start + 1)
                                 ELSE 0
                               END)) as total_halaman")
                )
                ->groupBy('s.id')
                ->get();

            $totalBelumMulai = $totalBerjalan = $totalSelesai = 0;
            foreach ($progress as $p) {
                $pages = (int) ($p->total_halaman ?? 0);
                if ($pages <= 0) {
                    $totalBelumMulai++;
                } elseif ($pages >= 604) {
                    $totalSelesai++;
                } else {
                    $totalBerjalan++;
                }
            }
        } else {
            // Fallback: hitung jumlah entri setoran per santri
            $progress = DB::table('santri as s')
                ->join('halaqoh_santri as hs', 'hs.santri_id', '=', 's.id')
                ->join('halaqoh as h', 'h.id', '=', 'hs.halaqoh_id')
                ->leftJoin('hafalan_quran as hq', 'hq.santri_id', '=', 's.id')
                ->where('h.guru_id', $guruId)
                ->select('s.id', DB::raw('COUNT(hq.id) as total_entri'))
                ->groupBy('s.id')
                ->get();

            $totalBelumMulai = $totalBerjalan = $totalSelesai = 0;
            foreach ($progress as $p) {
                $cnt = (int) ($p->total_entri ?? 0);
                if ($cnt <= 0) {
                    $totalBelumMulai++;
                } else {
                    $totalBerjalan++;
                }
            }
        }

        // ===============================
        // 📊 Rekap Hafalan per Juz (opsional, hanya jika kolomnya ada)
        // ===============================
        $rekapPerJuz = collect();
        $hasJuzStart = Schema::hasColumn('hafalan_quran', 'juz_start');
        $hasJuzEnd   = Schema::hasColumn('hafalan_quran', 'juz_end');
        if ($hasJuzStart || $hasJuzEnd) {
            $rekapPerJuz = DB::table('hafalan_quran as hq')
                ->join('halaqoh as h', 'h.id', '=', 'hq.halaqoh_id')
                ->where('h.guru_id', $guruId)
                ->whereMonth('hq.tanggal_setor', $bulan)
                ->whereYear('hq.tanggal_setor', $tahun)
                ->selectRaw('COALESCE(hq.juz_start, hq.juz_end) as juz, COUNT(hq.id) as total')
                ->groupBy('juz')
                ->orderBy('juz')
                ->pluck('total', 'juz');
        }

        // ===============================
        // 🕋 Rekap Surat Paling Disetorkan
        // ===============================
        $rekapSurat = DB::table('hafalan_quran as hq')
            ->join('halaqoh as h', 'h.id', '=', 'hq.halaqoh_id')
            ->where('h.guru_id', $guruId)
            ->whereMonth('hq.tanggal_setor', $bulan)
            ->whereYear('hq.tanggal_setor', $tahun)
            ->selectRaw("CONCAT('Surah ', COALESCE(hq.surah_id, 0)) as surat, COUNT(hq.id) as total")
            ->groupBy('surat')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'surat');

        // ===============================
        // ✅ Kirim ke View
        // ===============================
        return view('guru.dashboard', [
            'errorMessage'     => null,
            'totalSantri'      => $totalSantri,
            'totalHafalan'     => $totalHafalan,
            'hafalanPerMinggu' => $hafalanPerMinggu,
            'santriPerUnit'    => $santriPerUnit,
            'daftarSantri'     => $daftarSantri,
            'rekapPerJuz'      => $rekapPerJuz,
            'rekapSurat'       => $rekapSurat,
            'totalBelumMulai'  => $totalBelumMulai,
            'totalBerjalan'    => $totalBerjalan,
            'totalSelesai'     => $totalSelesai,
            'targetBulanan'    => $targetBulanan,
            'progressPersen'   => $progressPersen,
            'bulan'            => $bulan,
            'tahun'            => $tahun,
        ]);
    }
}
