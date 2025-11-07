<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ==============================
        // 📘 TABEL quran_surah
        // ==============================
        Schema::create('quran_surah', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('nama_surah', 100);
            $table->unsignedSmallInteger('jumlah_ayat');
            $table->timestamps();
        });

        // ==============================
        // 📖 TABEL quran_juz_map
        // ==============================
        Schema::create('quran_juz_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('juz');
            // ✅ gunakan foreignId agar otomatis BIGINT UNSIGNED
            $table->foreignId('surah_id')
                  ->constrained('quran_surah')
                  ->onDelete('cascade');
            $table->unsignedSmallInteger('ayat_awal')->nullable();
            $table->unsignedSmallInteger('ayat_akhir')->nullable();
            $table->timestamps();
        });

        // ==============================
        // 📄 TABEL quran_page_map
        // ==============================
        Schema::create('quran_page_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('page');
            $table->unsignedTinyInteger('juz')->nullable();
            // ✅ tipe sama (BIGINT UNSIGNED)
            $table->foreignId('surah_id')
                  ->nullable()
                  ->constrained('quran_surah')
                  ->onDelete('cascade');
            $table->unsignedSmallInteger('ayat_awal')->nullable();
            $table->unsignedSmallInteger('ayat_akhir')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('quran_page_map');
        Schema::dropIfExists('quran_juz_map');
        Schema::dropIfExists('quran_surah');
        Schema::enableForeignKeyConstraints();
    }
};
