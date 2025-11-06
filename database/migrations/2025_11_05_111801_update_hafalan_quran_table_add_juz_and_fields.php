<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hafalan_quran', function (Blueprint $table) {
            // Struktur tambahan agar sesuai dengan model & controller
            if (!Schema::hasColumn('hafalan_quran', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('hafalan_quran', 'guru_id')) {
                $table->unsignedBigInteger('guru_id')->nullable()->after('halaqoh_id');
            }
            if (!Schema::hasColumn('hafalan_quran', 'mode')) {
                $table->enum('mode', ['page', 'ayat'])->default('page')->after('tanggal_setor');
            }
            if (!Schema::hasColumn('hafalan_quran', 'page_start')) {
                $table->integer('page_start')->nullable()->after('mode');
                $table->integer('page_end')->nullable()->after('page_start');
            }
            if (!Schema::hasColumn('hafalan_quran', 'surah_id')) {
                $table->integer('surah_id')->nullable()->change();
            }
            if (!Schema::hasColumn('hafalan_quran', 'ayah_start')) {
                $table->integer('ayah_start')->nullable()->after('surah_id');
                $table->integer('ayah_end')->nullable()->after('ayah_start');
            }
            if (!Schema::hasColumn('hafalan_quran', 'juz_start')) {
                $table->integer('juz_start')->nullable()->after('ayah_end');
                $table->integer('juz_end')->nullable()->after('juz_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hafalan_quran', function (Blueprint $table) {
            $table->dropColumn([
                'unit_id', 'guru_id', 'mode',
                'page_start', 'page_end',
                'ayah_start', 'ayah_end',
                'juz_start', 'juz_end',
            ]);
        });
    }
};
