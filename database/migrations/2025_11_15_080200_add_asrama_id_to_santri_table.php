<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (!Schema::hasColumn('santri', 'asrama_id')) {
                $table->foreignId('asrama_id')
                    ->nullable()
                    ->after('unit_id')
                    ->constrained('asramas')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            if (Schema::hasColumn('santri', 'asrama_id')) {
                $table->dropConstrainedForeignId('asrama_id');
            }
        });
    }
};
