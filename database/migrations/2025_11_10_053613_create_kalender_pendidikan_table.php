<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kalender_pendidikan')) {
            return;
        }

        Schema::create('kalender_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->year('tahun_ajaran');
            $table->date('tanggal_mulai');
            $table->json('libur')->nullable();
            $table->text('event')->nullable();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_pendidikan');
    }
};
