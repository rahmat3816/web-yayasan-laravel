<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jabatan')) {
            Schema::create('jabatan', function (Blueprint $table) {
                $table->id();
                $table->string('nama_jabatan');
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['nama_jabatan', 'unit_id']); // Pastikan jabatan unik per unit
            });
        }

        if (!Schema::hasTable('guru_jabatan')) {
            Schema::create('guru_jabatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
                $table->foreignId('jabatan_id')->constrained('jabatan')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_jabatan');
        Schema::dropIfExists('jabatan');
    }
};
