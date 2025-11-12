<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mapel')) {
            Schema::create('mapel', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->enum('tipe', ['syari', 'umum']);
                $table->timestamps();
            });
        } else {
            Schema::table('mapel', function (Blueprint $table) {
                if (!Schema::hasColumn('mapel', 'nama')) {
                    $table->string('nama')->after('id');
                }

                if (!Schema::hasColumn('mapel', 'tipe')) {
                    $table->enum('tipe', ['syari', 'umum'])->after('nama');
                }

                if (!Schema::hasColumn('mapel', 'created_at')) {
                    $table->timestamps();
                }

                if (Schema::hasColumn('mapel', 'unit_id')) {
                    $database = DB::getDatabaseName();
                    $constraints = DB::select('
                        SELECT CONSTRAINT_NAME
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = ?
                          AND TABLE_NAME = ?
                          AND COLUMN_NAME = ?
                          AND REFERENCED_TABLE_NAME IS NOT NULL
                    ', [$database, 'mapel', 'unit_id']);

                    foreach ($constraints as $constraint) {
                        DB::statement('ALTER TABLE `mapel` DROP FOREIGN KEY `' . $constraint->CONSTRAINT_NAME . '`');
                    }

                    $table->dropColumn('unit_id');
                }
            });
        }

        if (!Schema::hasTable('guru_mapel')) {
            Schema::create('guru_mapel', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
                $table->foreignId('mapel_id')->constrained('mapel')->onDelete('cascade');
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mapel');
        Schema::dropIfExists('mapel');
    }
};
