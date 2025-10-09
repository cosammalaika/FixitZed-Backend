<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('coins');
            $table->unsignedInteger('valid_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default plans
        DB::table('subscription_plans')->insert([
            ['name' => 'Fixer Lite', 'price_cents' => 5000, 'coins' => 4,  'valid_days' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fixer Plus', 'price_cents' => 10000, 'coins' => 10, 'valid_days' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fixer Max',  'price_cents' => 15000, 'coins' => 16, 'valid_days' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

