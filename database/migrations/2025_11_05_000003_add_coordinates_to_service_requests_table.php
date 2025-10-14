<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'location_lat')) {
                $table->decimal('location_lat', 10, 7)->nullable()->after('location');
            }
            if (! Schema::hasColumn('service_requests', 'location_lng')) {
                $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'location_lng')) {
                $table->dropColumn('location_lng');
            }
            if (Schema::hasColumn('service_requests', 'location_lat')) {
                $table->dropColumn('location_lat');
            }
        });
    }
};
