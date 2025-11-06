<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('halaqoh', function (Blueprint $table) {
            $table->id();
            $table->string('nama_halaqoh');
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->timestamps();

            $table->foreign('guru_id')->references('id')->on('guru')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('unit')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqoh');
    }
};
