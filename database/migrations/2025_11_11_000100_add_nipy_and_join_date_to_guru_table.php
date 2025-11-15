<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'tanggal_bergabung')) {
                $table->date('tanggal_bergabung')
                    ->nullable()
                    ->after('alamat');
            }

            if (!Schema::hasColumn('guru', 'nipy')) {
                $table->string('nipy')
                    ->nullable()
                    ->unique()
                    ->after('tanggal_bergabung');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'nipy')) {
                $table->dropUnique('guru_nipy_unique');
                $table->dropColumn('nipy');
            }

            if (Schema::hasColumn('guru', 'tanggal_bergabung')) {
                $table->dropColumn('tanggal_bergabung');
            }
        });
    }
};
