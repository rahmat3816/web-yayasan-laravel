<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('unit') && !Schema::hasTable('units')) {
            Schema::rename('unit', 'units');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('units') && !Schema::hasTable('unit')) {
            Schema::rename('units', 'unit');
        }
    }
};
