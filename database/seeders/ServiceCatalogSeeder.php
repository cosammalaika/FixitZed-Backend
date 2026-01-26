<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Plumbing' => ['Leak Repair', 'Pipe Installation', 'Bathroom Fittings', 'Water Heater Installation', 'Drainage Unblocking', 'Toilet Repair & Installation', 'Kitchen Sink Installation', 'Septic Tank Maintenance', 'Shower & Bathtub Installation', 'Water Pump Installation'],
            'Electrical' => ['Wiring & Rewiring', 'Lighting Installation', 'Appliance Repair', 'Generator Installation', 'Solar Panel Installation', 'Socket Installation & Repair', 'Switch Installation & Repair', 'Circuit Breaker Replacement', 'Ceiling Fan Installation', 'Backup Power Solutions', 'Inverter Setup', 'Smart Lighting Setup'],
            'Cleaning' => ['House Cleaning', 'Carpet Cleaning', 'Sofa & Upholstery Cleaning', 'Deep Cleaning', 'Office Cleaning', 'Post-Construction Cleaning', 'Window Cleaning', 'Mattress Cleaning', 'Kitchen Degreasing'],
            'Carpentry' => ['Furniture Repair', 'Custom Furniture', 'Door & Window Repair', 'Wood Polishing', 'Cabinet Installation', 'Bed & Wardrobe Assembly', 'Decking & Pergola Building'],
            'Painting' => ['Interior Painting', 'Exterior Painting', 'Waterproofing', 'Wallpaper Installation', 'Decorative Wall Finishes', 'Spray Painting'],
            'Gardening & Landscaping' => ['Lawn Mowing', 'Garden Maintenance', 'Tree Trimming', 'Landscaping Design', 'Irrigation System Installation', 'Hedge Trimming', 'Garden Soil & Fertilizer Supply', 'Greenhouse Setup'],
            'Appliance Services' => ['Refrigerator Repair', 'Washing Machine Repair', 'Air Conditioner Service', 'Microwave Repair', 'Oven & Stove Repair', 'TV Mounting & Setup', 'Dishwasher Repair', 'Freezer Repair'],
            'Security Services' => ['CCTV Installation', 'Alarm System Installation', 'Security Fencing', 'Smart Lock Installation', 'Intercom System Setup', 'Motion Sensor Setup'],
            'Moving & Relocation' => ['House Moving', 'Office Relocation', 'Packing & Unpacking', 'Furniture Assembly', 'Storage Services', 'International Relocation'],
            'Roofing & Masonry' => ['Roof Repair', 'Roof Installation', 'Tiling', 'Bricklaying', 'Plastering & Skimming', 'Concrete Works'],
            'HVAC Services' => ['AC Installation', 'AC Repair', 'Ventilation System Setup', 'Heater Installation', 'Duct Cleaning'],
            'IT & Smart Home' => ['WiFi Setup', 'Smart TV Setup', 'Home Automation Devices', 'Computer & Laptop Repair', 'Smart Speaker Setup', 'Home Theater Installation'],
            'Custom Service' => ['Other - Specify Your Service'],
            'Electronics & Office Repair' => ['Mobile Phone Repair', 'Computer & Laptop Repair', 'Printer Repair', 'Office Equipment Repairs'],
        ];

        $this->deduplicateCatalog();

        foreach ($catalog as $category => $services) {
            foreach ($services as $svcName) {
                Service::firstOrCreate(
                    [
                        'name' => $svcName,
                        'category' => $category,
                    ],
                    [
                        'description' => $svcName,
                        'status' => 'active',
                    ]
                );
            }
        }
    }

    protected function deduplicateCatalog(): void
    {
        $duplicateServices = Service::select('name', 'category')
            ->groupBy('name', 'category')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateServices as $dup) {
            Service::where('name', $dup->name)
                ->where('category', $dup->category)
                ->orderBy('id')
                ->skip(1)
                ->delete();
        }
    }
}
