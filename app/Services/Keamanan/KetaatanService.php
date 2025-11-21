<?php

namespace App\Services\Keamanan;

use App\Models\KetaatanLog;
use App\Models\KetaatanType;
use App\Models\PelanggaranSantriStat;
use Illuminate\Support\Facades\DB;

class KetaatanService
{
    public function catat(array $data): KetaatanLog
    {
        return DB::transaction(function () use ($data) {
            $type = KetaatanType::findOrFail($data['ketaatan_type_id']);
            $poin = $data['poin'] ?? $type->poin_pengurang ?? 0;

            $log = KetaatanLog::create([
                'santri_id' => $data['santri_id'],
                'ketaatan_type_id' => $type->id,
                'poin' => $poin,
                'catatan' => $data['catatan'] ?? null,
                'dibuat_oleh' => $data['dibuat_oleh'] ?? null,
            ]);

            $stat = PelanggaranSantriStat::firstOrCreate(
                ['santri_id' => $data['santri_id']],
                [
                    'poin_pelanggaran' => 0,
                    'poin_penghargaan' => 0,
                    'total_poin' => 0,
                    'sp_level' => 0,
                ]
            );

            $stat->poin_penghargaan += $poin;
            $stat->total_poin = $stat->poin_pelanggaran - $stat->poin_penghargaan;

            // SP tidak diturunkan, tetap yang pernah dicapai.
            $stat->save();

            return $log;
        });
    }
}
