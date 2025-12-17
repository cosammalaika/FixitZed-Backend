<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->string('app', 32)->default('customer');
            $table->string('device_id', 191)->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->index('user_id');
            $table->index('app');
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['app']);
            $table->dropColumn(['app', 'device_id', 'last_seen_at']);
        });
    }
};
