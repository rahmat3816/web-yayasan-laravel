<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quran_page_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surah_id')->index(); // ✅ samakan tipe dengan quran_surah.id
            $table->smallInteger('page')->index();
            $table->smallInteger('ayat_awal');
            $table->smallInteger('ayat_akhir');
            $table->timestamps();

            $table->foreign('surah_id')
                  ->references('id')
                  ->on('quran_surah')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_page_map');
    }
};
