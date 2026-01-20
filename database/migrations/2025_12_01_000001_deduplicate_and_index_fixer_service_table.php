<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Remove duplicate rows keeping the earliest id per fixer/service pair.
        DB::statement('DELETE fs1 FROM fixer_service fs1 JOIN fixer_service fs2 ON fs1.fixer_id = fs2.fixer_id AND fs1.service_id = fs2.service_id AND fs1.id > fs2.id');

        Schema::table('fixer_service', function (Blueprint $table) {
            $table->unique(['fixer_id', 'service_id'], 'fixer_service_fixer_id_service_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('fixer_service', function (Blueprint $table) {
            $table->dropUnique('fixer_service_fixer_id_service_id_unique');
        });
    }
};
