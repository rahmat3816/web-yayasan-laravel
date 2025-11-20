<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutuns', function (Blueprint $table) {
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

        Schema::create('mutun_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('mutun_id')->constrained('mutuns')->cascadeOnDelete();
            $table->year('tahun');
            $table->string('semester')->nullable();
            $table->enum('status', ['belum_mulai', 'berjalan', 'selesai'])->default('berjalan');
            $table->timestamps();

            $table->unique(['santri_id', 'mutun_id', 'tahun', 'semester'], 'mutun_target_unique');
        });

        Schema::create('mutun_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('mutun_targets')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('penilai_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->unsignedTinyInteger('nilai_mutqin')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutun_setorans');
        Schema::dropIfExists('mutun_targets');
        Schema::dropIfExists('mutuns');
    }
};
