<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fixers', function (Blueprint $table) {
            if (! Schema::hasColumn('fixers', 'last_offered_at')) {
                $table->timestamp('last_offered_at')->nullable()->after('priority_points');
            }
            if (! Schema::hasColumn('fixers', 'last_assigned_at')) {
                $table->timestamp('last_assigned_at')->nullable()->after('last_offered_at');
            }
            if (! Schema::hasColumn('fixers', 'last_completed_at')) {
                $table->timestamp('last_completed_at')->nullable()->after('last_assigned_at');
            }
            if (! Schema::hasColumn('fixers', 'last_idle_bonus_at')) {
                $table->timestamp('last_idle_bonus_at')->nullable()->after('last_completed_at');
            }
            if (! Schema::hasColumn('fixers', 'priority_low_since_at')) {
                $table->timestamp('priority_low_since_at')->nullable()->after('last_idle_bonus_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fixers', function (Blueprint $table) {
            if (Schema::hasColumn('fixers', 'priority_low_since_at')) {
                $table->dropColumn('priority_low_since_at');
            }
            if (Schema::hasColumn('fixers', 'last_idle_bonus_at')) {
                $table->dropColumn('last_idle_bonus_at');
            }
            if (Schema::hasColumn('fixers', 'last_completed_at')) {
                $table->dropColumn('last_completed_at');
            }
            if (Schema::hasColumn('fixers', 'last_assigned_at')) {
                $table->dropColumn('last_assigned_at');
            }
            if (Schema::hasColumn('fixers', 'last_offered_at')) {
                $table->dropColumn('last_offered_at');
            }
        });
    }
};
