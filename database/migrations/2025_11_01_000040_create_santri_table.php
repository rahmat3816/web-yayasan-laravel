<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('no_hp_wali')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('unit')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
