<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_health_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_log_id')->constrained('santri_health_logs')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tindakan', [
                'observasi',
                'obat_ringan',
                'rujuk_klinik',
                'rujuk_puskesmas',
                'rujuk_rumahsakit',
                'lainnya',
            ])->default('observasi');
            $table->string('rujukan_tempat')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('instruksi_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_health_actions');
    }
};
