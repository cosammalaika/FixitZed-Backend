<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('address');
            }
            if (! Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'province')) {
                $table->dropColumn('province');
            }
            if (Schema::hasColumn('users', 'district')) {
                $table->dropColumn('district');
            }
        });
    }
};
