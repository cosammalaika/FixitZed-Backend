<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('mfa_secret')->nullable()->after('remember_token');
            $table->text('mfa_temp_secret')->nullable()->after('mfa_secret');
            $table->boolean('mfa_enabled')->default(false)->after('mfa_temp_secret');
            $table->json('mfa_backup_codes')->nullable()->after('mfa_enabled');
            $table->timestamp('mfa_last_confirmed_at')->nullable()->after('mfa_backup_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mfa_secret',
                'mfa_temp_secret',
                'mfa_enabled',
                'mfa_backup_codes',
                'mfa_last_confirmed_at',
            ]);
        });
    }
};
