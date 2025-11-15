<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('guru')) {
            return;
        }

        $sequence = (int) (DB::table('guru')
            ->whereNotNull('nipy')
            ->selectRaw('MAX(CAST(SUBSTR(nipy, -4) AS UNSIGNED)) as max_seq')
            ->value('max_seq') ?? 0);

        DB::table('guru')
            ->orderBy('id')
            ->chunkById(100, function ($gurus) use (&$sequence) {
                foreach ($gurus as $guru) {
                    if (!empty($guru->nipy)) {
                        continue;
                    }

                    $dateSource = $guru->tanggal_bergabung ?? $guru->created_at ?? now();
                    $joinDate = Carbon::parse($dateSource);
                    $prefix = $joinDate->format('Y.m');

                    do {
                        $sequence++;
                        $candidate = sprintf('%s.%04d', $prefix, $sequence);
                    } while (
                        DB::table('guru')->where('nipy', $candidate)->exists()
                    );

                    DB::table('guru')
                        ->where('id', $guru->id)
                        ->update([
                            'tanggal_bergabung' => $joinDate->toDateString(),
                            'nipy' => $candidate,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No down migration; keeping generated NIPY values.
    }
};
