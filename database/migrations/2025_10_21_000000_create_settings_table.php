<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            [
                'key' => 'currency.code',
                'value' => 'ZMW',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency.symbol',
                'value' => 'ZMW',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency.name',
                'value' => 'Zambian Kwacha',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
