<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('musyrif_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('asrama_id')->constrained('asramas')->cascadeOnDelete();
            $table->date('mulai_tugas');
            $table->date('selesai_tugas')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'selesai'])->default('aktif');
            $table->string('shift')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['asrama_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('musyrif_assignments');
    }
};
