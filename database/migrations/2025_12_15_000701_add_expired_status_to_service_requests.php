<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE service_requests MODIFY status ENUM('pending','accepted','completed','cancelled','awaiting_payment','expired') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE service_requests MODIFY status ENUM('pending','accepted','completed','cancelled','awaiting_payment') DEFAULT 'pending'");
    }
};
