<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan'); // Wakamad Kurikulum, Mudir, Koord Tahfizh Putra, dll
            $table->string('slug')->unique();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('guru_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatan')->onDelete('cascade');
            $table->unique(['user_id', 'jabatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_jabatan');
        Schema::dropIfExists('jabatan');
    }
};
