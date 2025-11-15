<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hafalan_quran', function (Blueprint $table) {
            $table->unsignedTinyInteger('penilaian_tajwid')->nullable()->after('status');
            $table->unsignedTinyInteger('penilaian_mutqin')->nullable()->after('penilaian_tajwid');
            $table->unsignedTinyInteger('penilaian_adab')->nullable()->after('penilaian_mutqin');
        });
    }

    public function down(): void
    {
        Schema::table('hafalan_quran', function (Blueprint $table) {
            $table->dropColumn(['penilaian_tajwid', 'penilaian_mutqin', 'penilaian_adab']);
        });
    }
};
