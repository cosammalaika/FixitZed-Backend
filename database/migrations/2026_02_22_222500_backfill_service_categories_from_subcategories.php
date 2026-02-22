<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('services') || ! Schema::hasTable('subcategories')) {
            return;
        }

        if (! Schema::hasColumn('services', 'subcategory_id') || ! Schema::hasColumn('services', 'category')) {
            return;
        }

        DB::table('services')
            ->join('subcategories', 'services.subcategory_id', '=', 'subcategories.id')
            ->where(function ($q) {
                $q->whereNull('services.category')
                    ->orWhere('services.category', '')
                    ->orWhere('services.category', 'General');
            })
            ->update(['services.category' => DB::raw('subcategories.name')]);
    }

    public function down(): void
    {
        // No-op: data backfill only.
    }
};
