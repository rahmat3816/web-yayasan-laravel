<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('guru_mapel')) {
            return;
        }

        if (!Schema::hasColumn('guru_mapel', 'unit_id')) {
            Schema::table('guru_mapel', function (Blueprint $table) {
                $table->foreignId('unit_id')
                    ->nullable()
                    ->after('mapel_id')
                    ->constrained('units')
                    ->cascadeOnDelete();
            });

            DB::statement('
                UPDATE guru_mapel gm
                JOIN guru g ON gm.guru_id = g.id
                SET gm.unit_id = g.unit_id
                WHERE gm.unit_id IS NULL
            ');

            $fallbackUnit = DB::table('units')->min('id');
            if ($fallbackUnit) {
                DB::table('guru_mapel')
                    ->whereNull('unit_id')
                    ->update(['unit_id' => $fallbackUnit]);
            }

            Schema::table('guru_mapel', function (Blueprint $table) {
                $table->unique(['guru_id', 'mapel_id', 'unit_id'], 'guru_mapel_unique_assignment');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('guru_mapel')) {
            return;
        }

        if (Schema::hasColumn('guru_mapel', 'unit_id')) {
            Schema::table('guru_mapel', function (Blueprint $table) {
                $table->dropUnique('guru_mapel_unique_assignment');
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }
    }
};
