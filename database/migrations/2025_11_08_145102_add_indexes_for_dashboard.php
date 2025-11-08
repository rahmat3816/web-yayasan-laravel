<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForDashboard extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (!Schema::hasColumn('santri', 'unit_id')) return;
            $table->index('unit_id', 'santri_unit_id_idx');
        });

        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'unit_id')) return;
            $table->index('unit_id', 'guru_unit_id_idx');
        });

        Schema::table('halaqoh', function (Blueprint $table) {
            if (!Schema::hasColumn('halaqoh', 'unit_id')) return;
            $table->index('unit_id', 'halaqoh_unit_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('santri', fn(Blueprint $t) => $t->dropIndex('santri_unit_id_idx'));
        Schema::table('guru', fn(Blueprint $t) => $t->dropIndex('guru_unit_id_idx'));
        Schema::table('halaqoh', fn(Blueprint $t) => $t->dropIndex('halaqoh_unit_id_idx'));
    }
}
