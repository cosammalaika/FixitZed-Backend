<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixer_id')->constrained('fixers')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('payment_reference')->nullable();
            $table->enum('status', ['pending','approved','failed','expired','refunded'])->default('pending');
            $table->unsignedInteger('coins_awarded')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['fixer_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixer_subscriptions');
    }
};

