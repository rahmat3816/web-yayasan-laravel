<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('wali_santri')->after('password');
            $table->foreignId('unit_id')->nullable()->after('role');
            $table->unsignedBigInteger('linked_guru_id')->nullable()->after('unit_id');
            $table->unsignedBigInteger('linked_santri_id')->nullable()->after('linked_guru_id');

            // index & relasi opsional
            $table->foreign('unit_id')->references('id')->on('unit')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['role', 'unit_id', 'linked_guru_id', 'linked_santri_id']);
        });
    }
};
