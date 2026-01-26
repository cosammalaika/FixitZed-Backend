<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   public function up()
{
    Schema::table('services', function (Blueprint $table) {
        // Ensure required columns exist
        if (! Schema::hasColumn('services', 'category')) {
            $table->string('category')->default('General')->after('name');
        }
        if (! Schema::hasColumn('services', 'status')) {
            $table->string('status')->default('active')->after('description');
        }
    });

    // Backfill category from legacy subcategory/category names when possible.
    if (Schema::hasColumn('services', 'subcategory_id')) {
        $hasSubcatTable = Schema::hasTable('subcategories');
        if ($hasSubcatTable) {
            DB::table('services')
                ->join('subcategories', 'services.subcategory_id', '=', 'subcategories.id')
                ->update(['services.category' => DB::raw('subcategories.name')]);
        }
    }

    Schema::table('services', function (Blueprint $table) {
        // Drop legacy columns and constraints safely
        if (Schema::hasColumn('services', 'subcategory_id')) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn('subcategory_id');
        }
        if (Schema::hasColumn('services', 'price')) {
            $table->dropColumn('price');
        }
        if (Schema::hasColumn('services', 'duration_minutes')) {
            $table->dropColumn('duration_minutes');
        }
        if (Schema::hasColumn('services', 'is_active')) {
            $table->dropColumn('is_active');
        }
    });
}

public function down()
{
    Schema::table('services', function (Blueprint $table) {
        if (Schema::hasColumn('services', 'status')) {
            $table->dropColumn('status');
        }
    });
}

};
