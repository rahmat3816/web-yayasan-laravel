<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('juz');
            $table->foreignId('surah_start_id')->constrained('quran_surah');
            $table->foreignId('surah_end_id')->constrained('quran_surah');
            $table->unsignedSmallInteger('ayat_start');
            $table->unsignedSmallInteger('ayat_end');
            $table->unsignedInteger('total_ayat');
            $table->unsignedInteger('target_per_bulan');
            $table->unsignedInteger('target_per_minggu');
            $table->unsignedInteger('target_per_hari');
            $table->timestamps();

            $table->unique(['santri_id', 'tahun'], 'hafalan_target_unique_per_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_targets');
    }
};

