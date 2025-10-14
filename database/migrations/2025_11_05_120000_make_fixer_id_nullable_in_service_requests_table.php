<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('service_requests')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            try {
                $table->dropForeign(['fixer_id']);
            } catch (\Throwable $e) {
                // ignore if foreign key missing
            }
        });

        DB::statement('ALTER TABLE service_requests MODIFY fixer_id BIGINT UNSIGNED NULL');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreign('fixer_id')
                ->references('id')
                ->on('fixers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_requests')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            try {
                $table->dropForeign(['fixer_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        DB::statement('ALTER TABLE service_requests MODIFY fixer_id BIGINT UNSIGNED NOT NULL');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreign('fixer_id')
                ->references('id')
                ->on('fixers')
                ->cascadeOnDelete();
        });
    }
};
