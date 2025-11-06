<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master Surah
        Schema::create('quran_surah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_surah');
            $table->integer('jumlah_ayat');
            $table->timestamps();
        });

        // Master Ayat
        Schema::create('quran_ayat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surah_id');
            $table->integer('nomor_ayat');
            $table->text('teks_arab')->nullable();
            $table->text('terjemahan')->nullable();
            $table->timestamps();

            $table->foreign('surah_id')->references('id')->on('quran_surah')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_ayat');
        Schema::dropIfExists('quran_surah');
    }
};
