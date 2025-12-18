<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class PestControlSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['name' => 'Pest Control'],
            ['description' => 'Pest control and fumigation services']
        );

        $items = [
            'General Pest Control' => 'General pest control',
            'Termite Control' => 'Termite control',
            'Cockroach Control' => 'Cockroach control',
            'Rodent Control' => 'Rodent control',
            'Bed Bug Treatment' => 'Bed bug treatment',
            'Mosquito/Fumigation' => 'Mosquito control and fumigation',
            'Ant Control' => 'Ant control',
            'Flea & Tick Control' => 'Flea and tick control',
            'General Fumigation' => 'General fumigation',
        ];

        foreach ($items as $name => $description) {
            $subcategory = Subcategory::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                ]
            );

            Service::updateOrCreate(
                [
                    'subcategory_id' => $subcategory->id,
                    'name' => $name,
                ],
                [
                    'description' => $description,
                    'price' => 0,
                    'duration_minutes' => 60,
                    'is_active' => true,
                ]
            );
        }
    }
}

