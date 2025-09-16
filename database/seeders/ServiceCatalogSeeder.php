<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Home Services' => [
                'Electrical' => [
                    'Light Installation',
                    'Socket Repair',
                    'Wiring Inspection',
                ],
                'Plumbing' => [
                    'Leak Fix',
                    'Drain Unclogging',
                    'Toilet Repair',
                    'Tap Replacement',
                ],
                'Cleaning' => [
                    'House Cleaning',
                    'Deep Cleaning',
                    'Move-in/Move-out Cleaning',
                ],
                'Appliances' => [
                    'Fridge Repair',
                    'Washing Machine Repair',
                    'Air Conditioner Service',
                    'Microwave Repair',
                ],
                'Carpentry' => [
                    'Furniture Repair',
                    'Door Installation',
                    'Wardrobe Assembly',
                ],
                'Painting' => [
                    'Interior Painting',
                    'Exterior Painting',
                    'Touch-up Painting',
                ],
            ],
            'Outdoor' => [
                'Gardening' => [
                    'Lawn Mowing',
                    'Hedge Trimming',
                    'Garden Cleanup',
                ],
                'Security' => [
                    'CCTV Installation',
                    'Electric Fence Repair',
                    'Gate Motor Service',
                ],
            ],
        ];

        foreach ($catalog as $categoryName => $subcats) {
            $category = Category::firstOrCreate([
                'name' => $categoryName,
            ], [
                'description' => $categoryName . ' related services',
            ]);

            foreach ($subcats as $subcatName => $services) {
                $subcategory = Subcategory::firstOrCreate([
                    'category_id' => $category->id,
                    'name' => $subcatName,
                ], [
                    'description' => $subcatName . ' services',
                ]);

                foreach ($services as $svcName) {
                    Service::firstOrCreate([
                        'subcategory_id' => $subcategory->id,
                        'name' => $svcName,
                    ], [
                        'description' => $svcName,
                        'price' => 0,
                        'duration_minutes' => 60,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}

