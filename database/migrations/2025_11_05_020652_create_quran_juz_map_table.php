<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('quran_juz_map')) {
            Schema::create('quran_juz_map', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('juz')->index();
                $table->unsignedBigInteger('surah_id')->index();
                $table->unsignedSmallInteger('ayat_awal');
                $table->unsignedSmallInteger('ayat_akhir');
                $table->timestamps();

                $table->foreign('surah_id')
                      ->references('id')
                      ->on('quran_surah')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_juz_map');
    }
};
