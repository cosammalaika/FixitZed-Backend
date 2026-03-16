<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('service_requests', 'cancellation_reason_key')) {
                    $table->string('cancellation_reason_key')->nullable()->after('status');
                }
                if (! Schema::hasColumn('service_requests', 'cancellation_reason_label')) {
                    $table->string('cancellation_reason_label')->nullable()->after('cancellation_reason_key');
                }
                if (! Schema::hasColumn('service_requests', 'cancellation_note')) {
                    $table->text('cancellation_note')->nullable()->after('cancellation_reason_label');
                }
                if (! Schema::hasColumn('service_requests', 'canceled_by')) {
                    $table->string('canceled_by', 32)->nullable()->after('cancellation_note');
                }
                if (! Schema::hasColumn('service_requests', 'canceled_at')) {
                    $table->timestamp('canceled_at')->nullable()->after('canceled_by');
                }
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (! Schema::hasColumn('notifications', 'data')) {
                    $table->json('data')->nullable()->after('message');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'data')) {
                    $table->dropColumn('data');
                }
            });
        }

        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $columns = [
                    'cancellation_reason_key',
                    'cancellation_reason_label',
                    'cancellation_note',
                    'canceled_by',
                    'canceled_at',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('service_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
