<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'work_photos')) {
                $table->json('work_photos')->nullable()->after('documents');
            }
        });

        Schema::table('fixers', function (Blueprint $table) {
            if (! Schema::hasColumn('fixers', 'accepted_terms_at')) {
                $table->timestamp('accepted_terms_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'work_photos')) {
                $table->dropColumn('work_photos');
            }
        });

        Schema::table('fixers', function (Blueprint $table) {
            if (Schema::hasColumn('fixers', 'accepted_terms_at')) {
                $table->dropColumn('accepted_terms_at');
            }
        });
    }
};
