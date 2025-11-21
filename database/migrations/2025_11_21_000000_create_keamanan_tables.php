<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('sp_threshold')->default(0);
            $table->timestamps();
        });

        Schema::create('pelanggaran_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('pelanggaran_categories')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('poin_default')->default(0);
            $table->boolean('langsung_sp3')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pelanggaran_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('pelanggaran_type_id')->constrained('pelanggaran_types')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('pelanggaran_categories')->cascadeOnDelete();
            $table->unsignedInteger('poin')->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('sp_level')->default(0);
            $table->timestamps();
        });

        Schema::create('pelanggaran_santri_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->unsignedInteger('total_poin')->default(0);
            $table->unsignedTinyInteger('sp_level')->default(0);
            $table->timestamps();
            $table->unique('santri_id');
        });

        Schema::create('pelanggaran_sp_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->unsignedTinyInteger('sp_level');
            $table->text('catatan')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_sp_histories');
        Schema::dropIfExists('pelanggaran_santri_stats');
        Schema::dropIfExists('pelanggaran_logs');
        Schema::dropIfExists('pelanggaran_types');
        Schema::dropIfExists('pelanggaran_categories');
    }
};
