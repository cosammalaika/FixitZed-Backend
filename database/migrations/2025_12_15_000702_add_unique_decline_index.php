<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_request_declines', function (Blueprint $table) {
            $table->unique(['service_request_id', 'fixer_id'], 'declines_request_fixer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_request_declines', function (Blueprint $table) {
            $table->dropUnique('declines_request_fixer_unique');
        });
    }
};
