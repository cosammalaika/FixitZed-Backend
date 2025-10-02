<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // reporter
            $table->string('type', 50)->default('user');
            $table->string('subject', 191);
            $table->text('message');
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('status', 50)->default('open'); // open|reviewed|action_taken|closed
            $table->string('action', 50)->nullable(); // none|warn|suspend|ban
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

