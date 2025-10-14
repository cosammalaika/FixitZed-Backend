<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fixers', function (Blueprint $table) {
            if (! Schema::hasColumn('fixers', 'priority_points')) {
                $table->unsignedSmallInteger('priority_points')->default(100)->after('rating_avg');
            }
            $table->index('priority_points');
        });

        Schema::create('priority_point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixer_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('delta');
            $table->unsignedSmallInteger('points_before')->nullable();
            $table->unsignedSmallInteger('points_after')->nullable();
            $table->string('reason', 64);
            $table->json('meta')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['fixer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_point_logs');

        Schema::table('fixers', function (Blueprint $table) {
            if (Schema::hasColumn('fixers', 'priority_points')) {
                $table->dropIndex('fixers_priority_points_index');
                $table->dropColumn('priority_points');
            }
        });
    }
};
