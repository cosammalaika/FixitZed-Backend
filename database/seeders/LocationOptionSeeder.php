<?php

namespace Database\Seeders;

use App\Models\LocationOption;
use Illuminate\Database\Seeder;

class LocationOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name' => 'Lusaka', 'is_active' => true],
            ['name' => 'Ndola', 'is_active' => true],
            ['name' => 'Kitwe', 'is_active' => true],
            ['name' => 'Livingstone', 'is_active' => true],
            ['name' => 'Kabwe', 'is_active' => true],
        ];

        foreach ($options as $opt) {
            LocationOption::firstOrCreate(['name' => $opt['name']], $opt);
        }
    }
}

