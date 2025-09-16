<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('address');
            $table->string('nrc_front_path')->nullable()->after('profile_photo_path');
            $table->string('nrc_back_path')->nullable()->after('nrc_front_path');
            $table->json('documents')->nullable()->after('nrc_back_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_photo_path', 'nrc_front_path', 'nrc_back_path', 'documents']);
        });
    }
};

