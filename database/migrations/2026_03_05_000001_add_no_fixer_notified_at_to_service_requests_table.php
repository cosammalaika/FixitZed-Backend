<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('service_requests', 'no_fixer_notified_at')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('no_fixer_notified_at')
                ->nullable()
                ->after('fixer_snoozed_until');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('service_requests', 'no_fixer_notified_at')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('no_fixer_notified_at');
        });
    }
};
