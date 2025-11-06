<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hafalan_quran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('halaqoh_id')->nullable();
            $table->unsignedBigInteger('santri_id')->nullable();
            $table->unsignedBigInteger('surah_id')->nullable();
            $table->integer('ayat_awal')->nullable();
            $table->integer('ayat_akhir')->nullable();
            $table->date('tanggal_setor')->nullable();
            $table->string('status')->default('belum_dinilai');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('halaqoh_id')->references('id')->on('halaqoh')->onDelete('set null');
            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('set null');
            $table->foreign('surah_id')->references('id')->on('quran_surah')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_quran');
    }
};
