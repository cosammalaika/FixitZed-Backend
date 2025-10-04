<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_request_declines')) {
            Schema::create('service_request_declines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('fixer_id')->constrained()->cascadeOnDelete();
                $table->timestamp('declined_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('service_requests', 'fixer_snoozed_until')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->timestamp('fixer_snoozed_until')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'fixer_snoozed_until')) {
                $table->dropColumn('fixer_snoozed_until');
            }
        });

        Schema::dropIfExists('service_request_declines');
    }
};
