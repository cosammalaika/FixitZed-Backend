<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add category column if missing (keep nullable to be backward compatible)
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
        });

        // Backfill null/empty categories to a safe default to avoid UI errors.
        if (Schema::hasColumn('services', 'category')) {
            DB::table('services')
                ->whereNull('category')
                ->orWhere('category', '')
                ->update(['category' => 'General']);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
