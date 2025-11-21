<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pelanggaran_santri_stats')) {
            Schema::table('pelanggaran_santri_stats', function (Blueprint $table) {
                if (! Schema::hasColumn('pelanggaran_santri_stats', 'poin_pelanggaran')) {
                    $table->unsignedInteger('poin_pelanggaran')->default(0)->after('santri_id');
                }
                if (! Schema::hasColumn('pelanggaran_santri_stats', 'poin_penghargaan')) {
                    $table->unsignedInteger('poin_penghargaan')->default(0)->after('poin_pelanggaran');
                }
                if (! Schema::hasColumn('pelanggaran_santri_stats', 'total_poin')) {
                    $table->integer('total_poin')->default(0)->after('poin_penghargaan');
                }
            });
        }

        Schema::create('ketaatan_types', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('poin_pengurang')->default(0);
            $table->text('catatan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('ketaatan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ketaatan_type_id')->constrained('ketaatan_types')->cascadeOnDelete();
            $table->unsignedInteger('poin')->default(0);
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ketaatan_logs');
        Schema::dropIfExists('ketaatan_types');

        if (Schema::hasTable('pelanggaran_santri_stats')) {
            Schema::table('pelanggaran_santri_stats', function (Blueprint $table) {
                if (Schema::hasColumn('pelanggaran_santri_stats', 'poin_pelanggaran')) {
                    $table->dropColumn('poin_pelanggaran');
                }
                if (Schema::hasColumn('pelanggaran_santri_stats', 'poin_penghargaan')) {
                    $table->dropColumn('poin_penghargaan');
                }
                if (Schema::hasColumn('pelanggaran_santri_stats', 'total_poin')) {
                    $table->dropColumn('total_poin');
                }
            });
        }
    }
};
