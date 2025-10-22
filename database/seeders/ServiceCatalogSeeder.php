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
        // Remove legacy umbrella categories to avoid duplicates.
        Category::whereIn('name', ['Home Services', 'Outdoor'])->each(function (Category $category) {
            $category->delete();
        });

        $catalog = [
            'Plumbing' => [
                'Leak Repair',
                'Pipe Installation',
                'Bathroom Fittings',
                'Water Heater Installation',
                'Drainage Unblocking',
                'Toilet Repair & Installation',
                'Kitchen Sink Installation',
                'Septic Tank Maintenance',
                'Shower & Bathtub Installation',
                'Water Pump Installation',
            ],
            'Electrical' => [
                'Wiring & Rewiring',
                'Lighting Installation',
                'Appliance Repair',
                'Generator Installation',
                'Solar Panel Installation',
                'Socket Installation & Repair',
                'Switch Installation & Repair',
                'Circuit Breaker Replacement',
                'Ceiling Fan Installation',
                'Backup Power Solutions',
                'Inverter Setup',
                'Smart Lighting Setup',
            ],
            'Cleaning' => [
                'House Cleaning',
                'Carpet Cleaning',
                'Sofa & Upholstery Cleaning',
                'Deep Cleaning',
                'Office Cleaning',
                'Post-Construction Cleaning',
                'Window Cleaning',
                'Mattress Cleaning',
                'Kitchen Degreasing',
            ],
            'Carpentry' => [
                'Furniture Repair',
                'Custom Furniture',
                'Door & Window Repair',
                'Wood Polishing',
                'Cabinet Installation',
                'Bed & Wardrobe Assembly',
                'Decking & Pergola Building',
            ],
            'Painting' => [
                'Interior Painting',
                'Exterior Painting',
                'Waterproofing',
                'Wallpaper Installation',
                'Decorative Wall Finishes',
                'Spray Painting',
            ],
            'Gardening & Landscaping' => [
                'Lawn Mowing',
                'Garden Maintenance',
                'Tree Trimming',
                'Landscaping Design',
                'Irrigation System Installation',
                'Hedge Trimming',
                'Garden Soil & Fertilizer Supply',
                'Greenhouse Setup',
            ],
            'Pest Control' => [
                'General Pest Control',
                'Termite Treatment',
                'Rodent Control',
                'Fumigation',
                'Bed Bug Treatment',
                'Mosquito Control',
                'Cockroach Control',
            ],
            'Appliance Services' => [
                'Refrigerator Repair',
                'Washing Machine Repair',
                'Air Conditioner Service',
                'Microwave Repair',
                'Oven & Stove Repair',
                'TV Mounting & Setup',
                'Dishwasher Repair',
                'Freezer Repair',
            ],
            'Security Services' => [
                'CCTV Installation',
                'Alarm System Installation',
                'Security Fencing',
                'Smart Lock Installation',
                'Intercom System Setup',
                'Motion Sensor Setup',
            ],
            'Moving & Relocation' => [
                'House Moving',
                'Office Relocation',
                'Packing & Unpacking',
                'Furniture Assembly',
                'Storage Services',
                'International Relocation',
            ],
            'Roofing & Masonry' => [
                'Roof Repair',
                'Roof Installation',
                'Tiling',
                'Bricklaying',
                'Plastering & Skimming',
                'Concrete Works',
            ],
            'HVAC Services' => [
                'AC Installation',
                'AC Repair',
                'Ventilation System Setup',
                'Heater Installation',
                'Duct Cleaning',
            ],
            'IT & Smart Home' => [
                'WiFi Setup',
                'Smart TV Setup',
                'Home Automation Devices',
                'Computer Repair',
                'Smart Speaker Setup',
                'Home Theater Installation',
            ],
            'Custom Service' => [
                'Other - Specify Your Service',
            ],
        ];

        $category = Category::firstOrCreate(
            ['name' => 'All Services'],
            ['description' => 'Complete FixitZed catalog']
        );

        // Reset subcategories under the consolidated category.
        Subcategory::where('category_id', $category->id)->delete();

        foreach ($catalog as $subcatName => $services) {
            $subcategory = Subcategory::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $subcatName,
                ],
                [
                    'description' => $subcatName . ' services',
                ]
            );

            foreach ($services as $svcName) {
                Service::firstOrCreate(
                    [
                        'subcategory_id' => $subcategory->id,
                        'name' => $svcName,
                    ],
                    [
                        'description' => $svcName,
                        'price' => 0,
                        'duration_minutes' => 60,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
