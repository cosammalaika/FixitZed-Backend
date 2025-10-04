<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('earnings')) {
            return;
        }

        if (Schema::hasColumn('earnings', 'service_count')) {
            Schema::table('earnings', function (Blueprint $table) {
                try {
                    $table->dropForeign(['service_count']);
                } catch (\Throwable $e) {
                    // Foreign key may not exist; ignore.
                }
                $table->dropColumn('service_count');
            });
        }

        Schema::table('earnings', function (Blueprint $table) {
            if (! Schema::hasColumn('earnings', 'service_count')) {
                $table->unsignedInteger('service_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('earnings')) {
            return;
        }

        Schema::table('earnings', function (Blueprint $table) {
            if (Schema::hasColumn('earnings', 'service_count')) {
                $table->dropColumn('service_count');
            }
        });

        Schema::table('earnings', function (Blueprint $table) {
            if (! Schema::hasColumn('earnings', 'service_count')) {
                $table->foreignId('service_count')->constrained('service_requests')->cascadeOnDelete();
            }
        });
    }
};
