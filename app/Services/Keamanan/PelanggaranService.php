<?php

namespace App\Services\Keamanan;

use App\Models\PelanggaranLog;
use App\Models\PelanggaranSantriStat;
use App\Models\PelanggaranSpHistory;
use App\Models\PelanggaranType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PelanggaranService
{
    /**
     * Catat pelanggaran dan perbarui poin + status SP.
     */
    public function catat(array $data): PelanggaranLog
    {
        return DB::transaction(function () use ($data) {
            $type = PelanggaranType::with('kategori')->findOrFail($data['pelanggaran_type_id']);

            $poin = $data['poin'] ?? $type->poin_default ?? 0;
            $kategoriId = $type->kategori_id;
            $createdAt = isset($data['created_at']) ? Carbon::parse($data['created_at']) : null;

            $log = PelanggaranLog::create([
                'santri_id' => $data['santri_id'],
                'pelanggaran_type_id' => $type->id,
                'kategori_id' => $kategoriId,
                'poin' => $poin,
                'catatan' => $data['catatan'] ?? null,
                'dibuat_oleh' => $data['dibuat_oleh'] ?? null,
                'sp_level' => 0,
                'created_at' => $createdAt,
            ]);

            // Update stats poin santri.
            $stat = PelanggaranSantriStat::firstOrCreate(
                ['santri_id' => $data['santri_id']],
                [
                    'poin_pelanggaran' => 0,
                    'poin_penghargaan' => 0,
                    'total_poin' => 0,
                    'sp_level' => 0,
                ]
            );

            $stat->poin_pelanggaran += $poin;
            $stat->total_poin = $stat->poin_pelanggaran - $stat->poin_penghargaan;

            $spLevel = $stat->sp_level;
            $spLevel = $this->hitungSpLevel(
                totalPoin: $stat->total_poin,
                langsungSp3: $type->langsung_sp3,
                currentSp: $spLevel
            );

            if ($spLevel > $stat->sp_level) {
                PelanggaranSpHistory::create([
                    'santri_id' => $data['santri_id'],
                    'sp_level' => $spLevel,
                    'catatan' => $data['catatan'] ?? null,
                    'issued_by' => $data['dibuat_oleh'] ?? null,
                ]);
            }

            $stat->sp_level = $spLevel;
            $stat->save();

            $log->update(['sp_level' => $spLevel]);

            return $log;
        });
    }

    protected function hitungSpLevel(int $totalPoin, bool $langsungSp3, int $currentSp): int
    {
        if ($langsungSp3 || $totalPoin >= 300) {
            return max($currentSp, 3);
        }

        if ($totalPoin >= 200) {
            return max($currentSp, 2);
        }

        if ($totalPoin >= 100) {
            return max($currentSp, 1);
        }

        return $currentSp;
    }
}
