<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('nip')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
