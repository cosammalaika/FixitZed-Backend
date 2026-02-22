<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'category')) {
                $table->string('category')->nullable()->after('name');
            }

            if (! Schema::hasColumn('services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        if (Schema::hasColumn('services', 'category') && Schema::hasColumn('services', 'subcategory_id') && Schema::hasTable('subcategories')) {
            DB::table('services')
                ->join('subcategories', 'services.subcategory_id', '=', 'subcategories.id')
                ->where(function ($q) {
                    $q->whereNull('services.category')
                        ->orWhere('services.category', '');
                })
                ->update(['services.category' => DB::raw('subcategories.name')]);
        }

        if (Schema::hasColumn('services', 'category')) {
            DB::table('services')
                ->whereNull('category')
                ->orWhere('category', '')
                ->update(['category' => 'General']);
        }

        if (Schema::hasColumn('services', 'is_active') && Schema::hasColumn('services', 'status')) {
            DB::table('services')
                ->whereRaw('LOWER(COALESCE(status, "")) IN (?, ?, ?, ?, ?)', ['inactive', '0', 'false', 'disabled', 'no'])
                ->update(['is_active' => 0]);

            DB::table('services')
                ->whereRaw('LOWER(COALESCE(status, "")) IN (?, ?, ?, ?, ?, ?)', ['active', '1', 'true', 'enabled', 'yes', 'on'])
                ->update(['is_active' => 1]);
        }

        if (Schema::hasColumn('services', 'status')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        if (! Schema::hasColumn('services', 'status')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('status')->nullable()->after('description');
            });
        }

        if (Schema::hasColumn('services', 'status') && Schema::hasColumn('services', 'is_active')) {
            DB::table('services')->update([
                'status' => DB::raw("CASE WHEN COALESCE(is_active, 0) = 1 THEN 'active' ELSE 'inactive' END"),
            ]);
        }
    }
};
