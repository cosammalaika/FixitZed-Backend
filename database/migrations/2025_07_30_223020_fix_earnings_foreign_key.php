<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('earnings', function (Blueprint $table) {
            if (!Schema::hasColumn('earnings', 'service_count')) {
                $table->unsignedInteger('service_count')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropColumn('service_count');
        });
    }
};
