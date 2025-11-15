<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (!Schema::hasColumn('santri', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true);
            }

            if (!Schema::hasColumn('santri', 'foto')) {
                $table->string('foto')->nullable()->after('status_aktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (Schema::hasColumn('santri', 'foto')) {
                $table->dropColumn('foto');
            }

            if (Schema::hasColumn('santri', 'status_aktif')) {
                $table->dropColumn('status_aktif');
            }
        });
    }
};
