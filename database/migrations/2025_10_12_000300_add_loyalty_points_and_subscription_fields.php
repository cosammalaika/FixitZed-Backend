<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points')->default(0)->after('documents');
        });

        Schema::table('fixer_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('amount_paid_cents')->default(0)->after('coins_awarded');
            $table->unsignedInteger('loyalty_points_used')->default(0)->after('amount_paid_cents');
            $table->unsignedInteger('loyalty_points_awarded')->default(0)->after('loyalty_points_used');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points_used')->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('loyalty_points');
        });

        Schema::table('fixer_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['amount_paid_cents', 'loyalty_points_used', 'loyalty_points_awarded']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('loyalty_points_used');
        });
    }
};
