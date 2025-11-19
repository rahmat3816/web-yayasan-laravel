<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hadits', function (Blueprint $table) {
            if (! Schema::hasColumn('hadits', 'nomor')) {
                $table->unsignedInteger('nomor')->nullable()->after('bab');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hadits', function (Blueprint $table) {
            if (Schema::hasColumn('hadits', 'nomor')) {
                $table->dropColumn('nomor');
            }
        });
    }
};
