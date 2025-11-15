<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->nullable()->unique()->after('name');
                }

                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('guru')->after('username');
                } else {
                    DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) DEFAULT 'guru'");
                }

                if (!Schema::hasColumn('users', 'unit_id')) {
                    $table->foreignId('unit_id')
                        ->nullable()
                        ->after('role')
                        ->constrained('units')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('users', 'linked_guru_id')) {
                    $table->foreignId('linked_guru_id')
                        ->nullable()
                        ->after('unit_id')
                        ->constrained('guru')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('users', 'linked_santri_id')) {
                    $table->foreignId('linked_santri_id')
                        ->nullable()
                        ->after('linked_guru_id')
                        ->constrained('santri')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('guru_jabatan')) {
            $this->migratePivotUnitIds();
            $this->deduplicateJabatanAssignments();

            Schema::table('guru_jabatan', function (Blueprint $table) {
                if (!Schema::hasColumn('guru_jabatan', 'unit_id')) {
                    $table->foreignId('unit_id')
                        ->nullable()
                        ->after('jabatan_id')
                        ->constrained('units')
                        ->cascadeOnDelete();
                }

                $table->unique(
                    ['jabatan_id', 'unit_id'],
                    'guru_jabatan_unique_per_unit'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('guru_jabatan')) {
            Schema::table('guru_jabatan', function (Blueprint $table) {
                if (Schema::hasColumn('guru_jabatan', 'jabatan_id')
                    && Schema::hasColumn('guru_jabatan', 'unit_id')
                ) {
                    $table->dropUnique('guru_jabatan_unique_per_unit');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'linked_santri_id')) {
                    $table->dropConstrainedForeignId('linked_santri_id');
                }
                if (Schema::hasColumn('users', 'linked_guru_id')) {
                    $table->dropConstrainedForeignId('linked_guru_id');
                }
                if (Schema::hasColumn('users', 'unit_id')) {
                    $table->dropConstrainedForeignId('unit_id');
                }
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }

    protected function deduplicateJabatanAssignments(): void
    {
        $duplicates = DB::table('guru_jabatan')
            ->select('jabatan_id', 'unit_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('unit_id')
            ->groupBy('jabatan_id', 'unit_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('guru_jabatan')
                ->where('jabatan_id', $duplicate->jabatan_id)
                ->where('unit_id', $duplicate->unit_id)
                ->orderBy('id')
                ->skip(1)
                ->take(PHP_INT_MAX)
                ->delete();
        }
    }

    protected function migratePivotUnitIds(): void
    {
        if (!Schema::hasColumn('guru_jabatan', 'unit_id')) {
            return;
        }

        if (!Schema::hasColumn('jabatan', 'unit_id')) {
            return;
        }

        $assignments = DB::table('guru_jabatan')
            ->whereNull('unit_id')
            ->get();

        if ($assignments->isEmpty()) {
            return;
        }

        foreach ($assignments as $assignment) {
            $unitId = DB::table('jabatan')
                ->where('id', $assignment->jabatan_id)
                ->value('unit_id');

            if ($unitId) {
                DB::table('guru_jabatan')
                    ->where('id', $assignment->id)
                    ->update(['unit_id' => $unitId]);
            }
        }
    }
};
