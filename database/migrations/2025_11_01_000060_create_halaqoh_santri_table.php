<?php
// database/migrations/2025_11_05_021000_create_halaqoh_santri_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('halaqoh_santri', function (Blueprint $table) {
            $table->unsignedBigInteger('halaqoh_id');
            $table->unsignedBigInteger('santri_id');
            $table->primary(['halaqoh_id','santri_id']);
            $table->foreign('halaqoh_id')->references('id')->on('halaqoh')->onDelete('cascade');
            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('halaqoh_santri');
    }
};
