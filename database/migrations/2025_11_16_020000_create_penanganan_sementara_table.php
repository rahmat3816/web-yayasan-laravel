<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penanganan_sementara', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::table('santri_health_logs', function (Blueprint $table) {
            $table->foreignId('penanganan_id')->nullable()->after('penanganan_sementara')->constrained('penanganan_sementara');
        });
    }

    public function down(): void
    {
        Schema::table('santri_health_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penanganan_id');
        });

        Schema::dropIfExists('penanganan_sementara');
    }
};
