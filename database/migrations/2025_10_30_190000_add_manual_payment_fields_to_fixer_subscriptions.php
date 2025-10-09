<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixer_subscriptions', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('system')->after('subscription_plan_id');
            $table->text('payment_instructions')->nullable()->after('payment_method');
            $table->json('payment_meta')->nullable()->after('payment_instructions');
            $table->timestamp('approved_at')->nullable()->after('expires_at');
            $table->timestamp('loyalty_deducted_at')->nullable()->after('approved_at');
            $table->timestamp('loyalty_awarded_at')->nullable()->after('loyalty_deducted_at');
        });
    }
    public function down(): void
    {
        Schema::table('fixer_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_instructions',
                'payment_meta',
                'approved_at',
                'loyalty_deducted_at',
                'loyalty_awarded_at',
            ]);
        });
    }
};

