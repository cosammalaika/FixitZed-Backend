<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixer_id')->constrained('fixers')->onDelete('cascade');
            $table->integer('delta'); // positive (credit) or negative (debit)
            $table->string('reason'); // purchase, service_request_accept, admin_adjustment
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['fixer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_ledgers');
    }
};

