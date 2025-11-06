<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan perubahan ke database.
     */
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            // 🆕 Tambah kolom NISN & NISY (unik)
            if (!Schema::hasColumn('santri', 'nisn')) {
                $table->string('nisn', 20)->nullable()->unique()->after('nama')
                      ->comment('Nomor Induk Santri Nasional (dari pemerintah)');
            }

            if (!Schema::hasColumn('santri', 'nisy')) {
                $table->string('nisy', 20)->unique()->after('nisn')
                      ->comment('Nomor Induk Santri Yayasan (digenerate otomatis)');
            }

            // 🗓️ Tambah kolom tahun masuk
            if (!Schema::hasColumn('santri', 'tahun_masuk')) {
                $table->year('tahun_masuk')->nullable()->after('unit_id')
                      ->comment('Tahun masuk santri ke unit yayasan');
            }
        });
    }

    /**
     * Batalkan perubahan.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'nisy', 'tahun_masuk']);
        });
    }
};
