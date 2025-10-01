<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE service_requests MODIFY status ENUM('pending','accepted','completed','cancelled','awaiting_payment') DEFAULT 'pending'");
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','accepted','in_progress','completed','cancelled','paid') DEFAULT 'pending'");
        } else {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
            Schema::table('payments', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE service_requests MODIFY status ENUM('pending','accepted','completed','cancelled') DEFAULT 'pending'");
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','accepted','in_progress','completed','cancelled') DEFAULT 'pending'");
        } else {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
            Schema::table('payments', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
        }
    }
};
