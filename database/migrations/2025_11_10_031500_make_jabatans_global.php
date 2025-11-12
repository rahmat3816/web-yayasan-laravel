<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (!Schema::hasColumn('guru_jabatan', 'unit_id')) {
            Schema::table('guru_jabatan', function (Blueprint $table) {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('jabatan_id')
                    ->constrained('units')
                    ->cascadeOnDelete();
            });
        }

        // Copy the unit info from jabatan rows to pivot rows.
        DB::statement('
            UPDATE guru_jabatan gj
            JOIN jabatan j ON gj.jabatan_id = j.id
            SET gj.unit_id = j.unit_id
        ');

        // Ensure every jabatan name is unique and keep a single record per job title.
        $grouped = DB::table('jabatan')
            ->select('id', 'nama_jabatan', 'slug', 'unit_id')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($row) => Str::slug($row->nama_jabatan));

        foreach ($grouped as $slug => $rows) {
            /** @var \Illuminate\Support\Collection<int, object> $rows */
            $primary = $rows->shift();

            // Update slug to be global (without unit suffix).
            DB::table('jabatan')
                ->where('id', $primary->id)
                ->update([
                    'slug' => $slug,
                    'unit_id' => null,
                ]);

            foreach ($rows as $duplicate) {
                DB::table('guru_jabatan')
                    ->where('jabatan_id', $duplicate->id)
                    ->update(['jabatan_id' => $primary->id]);

                DB::table('jabatan')
                    ->where('id', $duplicate->id)
                    ->delete();
            }
        }

        if (Schema::hasColumn('jabatan', 'unit_id')) {
            Schema::table('jabatan', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }

        // The column now only contains valid unit references, make it required.
        DB::statement('ALTER TABLE guru_jabatan MODIFY unit_id BIGINT UNSIGNED NOT NULL');

        Schema::table('guru_jabatan', function (Blueprint $table) {
            $table->unique(['user_id', 'jabatan_id', 'unit_id'], 'guru_jabatan_user_jabatan_unit_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('jabatan', 'unit_id')) {
            Schema::table('jabatan', function (Blueprint $table) {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('slug')
                    ->constrained('units')
                    ->cascadeOnDelete();
            });
        }

        $allJabatan = DB::table('jabatan')->get();

        foreach ($allJabatan as $jabatan) {
            $unitIds = DB::table('guru_jabatan')
                ->where('jabatan_id', $jabatan->id)
                ->distinct()
                ->pluck('unit_id')
                ->all();

            if (empty($unitIds)) {
                DB::table('jabatan')
                    ->where('id', $jabatan->id)
                    ->update(['unit_id' => null]);
                continue;
            }

            $firstUnit = array_shift($unitIds);

            DB::table('jabatan')
                ->where('id', $jabatan->id)
                ->update([
                    'unit_id' => $firstUnit,
                    'slug' => Str::slug($jabatan->nama_jabatan) . '-unit-' . $firstUnit,
                ]);

            foreach ($unitIds as $unitId) {
                $newId = DB::table('jabatan')->insertGetId([
                    'nama_jabatan' => $jabatan->nama_jabatan,
                    'slug' => Str::slug($jabatan->nama_jabatan) . '-unit-' . $unitId . '-' . Str::random(4),
                    'unit_id' => $unitId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('guru_jabatan')
                    ->where('jabatan_id', $jabatan->id)
                    ->where('unit_id', $unitId)
                    ->update(['jabatan_id' => $newId]);
            }
        }

        if (Schema::hasColumn('guru_jabatan', 'unit_id')) {
            Schema::table('guru_jabatan', function (Blueprint $table) {
                $table->dropUnique('guru_jabatan_user_jabatan_unit_unique');
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }
    }
};
