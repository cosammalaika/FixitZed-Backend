<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixer_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixer_id')->constrained('fixers')->onDelete('cascade');
            $table->unsignedInteger('coin_balance')->default(0);
            $table->enum('subscription_status', ['approved','pending'])->default('pending');
            $table->timestamp('last_subscription_expires_at')->nullable();
            $table->timestamps();
            $table->unique(['fixer_id']);
        });

        // Create wallets for existing fixers
        DB::table('fixers')->select('id')->orderBy('id')->chunk(100, function ($rows) {
            $now = now();
            $inserts = [];
            foreach ($rows as $r) {
                $inserts[] = [
                    'fixer_id' => $r->id,
                    'coin_balance' => 0,
                    'subscription_status' => 'pending',
                    'last_subscription_expires_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (!empty($inserts)) {
                DB::table('fixer_wallets')->insert($inserts);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixer_wallets');
    }
};

