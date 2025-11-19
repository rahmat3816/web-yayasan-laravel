<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hadits', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kitab')->nullable();
            $table->string('bab')->nullable();
            $table->unsignedInteger('nomor')->nullable();
            $table->unsignedInteger('urutan')->nullable();
            $table->text('teks_arab')->nullable();
            $table->text('teks_terjemah')->nullable();
            $table->timestamps();
        });

        Schema::create('hadits_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hadits_id')->constrained('hadits')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('teks')->nullable();
            $table->timestamps();
        });

        Schema::create('hadits_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('hadits_id')->constrained('hadits')->cascadeOnDelete();
            $table->year('tahun');
            $table->string('semester')->nullable();
            $table->enum('status', ['belum_mulai', 'berjalan', 'selesai'])->default('belum_mulai');
            $table->timestamps();

            $table->unique(['santri_id', 'hadits_id', 'tahun', 'semester'], 'hadits_target_unique');
        });

        Schema::create('hadits_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('hadits_targets')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('penilai_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->unsignedTinyInteger('nilai_tajwid')->nullable();
            $table->unsignedTinyInteger('nilai_mutqin')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('hadits_setoran_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setoran_id')->constrained('hadits_setorans')->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained('hadits_segments')->cascadeOnDelete();
            $table->enum('status', ['belum', 'ulang', 'lulus'])->default('belum');
            $table->timestamps();

            $table->unique(['setoran_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hadits_setoran_details');
        Schema::dropIfExists('hadits_setorans');
        Schema::dropIfExists('hadits_targets');
        Schema::dropIfExists('hadits_segments');
        Schema::dropIfExists('hadits');
    }
};
