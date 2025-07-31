<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('recipient_type', ['Customer', 'Fixer', 'Admin', 'Support', 'Individual'])->default('Individual');
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('recipient_type');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

};
