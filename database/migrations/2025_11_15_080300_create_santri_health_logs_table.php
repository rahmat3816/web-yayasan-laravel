<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_health_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('asrama_id')->nullable()->constrained('asramas')->nullOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('musyrif_assignment_id')->nullable()->constrained('musyrif_assignments')->nullOnDelete();
            $table->date('tanggal_sakit');
            $table->string('keluhan');
            $table->enum('tingkat', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->text('penanganan_sementara')->nullable();
            $table->enum('status', ['menunggu', 'ditangani', 'dirujuk', 'selesai'])->default('menunggu');
            $table->boolean('perlu_rujukan')->default(false);
            $table->timestamps();

            $table->index(['asrama_id', 'status']);
            $table->index(['santri_id', 'tanggal_sakit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_health_logs');
    }
};
