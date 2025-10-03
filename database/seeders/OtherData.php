<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Service;
use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\Earning;
use App\Models\Rating;
use App\Models\Coupon;
use App\Models\Review;
use App\Models\Location;
use App\Models\Notification;

class OtherData extends Seeder
{
    public function run(): void
    {
        // Users
        $customer = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'contact_number' => '0970000001',
            'user_type' => 'customer',
            'status' => 'Active',
            'address' => 'Lusaka, Zambia',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $fixerUser = User::create([
            'first_name' => 'Mary',
            'last_name' => 'Fixer',
            'username' => 'maryfix',
            'email' => 'mary@example.com',
            'contact_number' => '0970000002',
            'user_type' => 'fixer',
            'status' => 'Active',
            'address' => 'Ndola, Zambia',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Categories
        $plumbing = Category::create(['name' => 'Plumbing', 'description' => 'All plumbing related services']);
        $electrical = Category::create(['name' => 'Electrical', 'description' => 'Electrical services']);

        // Subcategories
        $pipeRepair = Subcategory::create(['category_id' => $plumbing->id, 'name' => 'Pipe Repair', 'description' => 'Fix leaking pipes']);
        $wiring = Subcategory::create(['category_id' => $electrical->id, 'name' => 'Wiring', 'description' => 'Install electrical wiring']);

        // Services
        $service = Service::create([
            'subcategory_id' => $pipeRepair->id,
            'name' => 'Fix leaking pipe',
            'description' => 'Bathroom or kitchen pipe repair',
            'price' => 150.00,
            'duration_minutes' => 60,
        ]);

        // Fixer
        $fixer = Fixer::create([
            'user_id' => $fixerUser->id,
            'bio' => 'Experienced in plumbing and wiring',
            'status' => 'approved',
            'rating_avg' => 4.5,
        ]);

        // Service Request
        $serviceRequest = ServiceRequest::create([
            'customer_id' => $customer->id,
            'fixer_id' => $fixer->id,
            'service_id' => $service->id,
            'scheduled_at' => now()->addDays(1),
            'status' => 'pending',
            'location' => 'Chilenje, Lusaka',
        ]);

        // Payment
        Payment::create([
            'service_request_id' => $serviceRequest->id,
            'amount' => 150.00,
            'status' => 'completed',
            'payment_method' => 'Airtel Money',
            'transaction_id' => Str::uuid(),
            'paid_at' => now(),
        ]);

        // Earning
        Earning::create([
            'fixer_id' => $fixer->id,
            'service_count' => 1,
            'amount' => 120.00,
        ]);

        // Rating
        Rating::create([
            'rater_id' => $customer->id,
            'rated_user_id' => $fixerUser->id,
            'service_request_id' => $serviceRequest->id,
            'role' => 'customer',
            'rating' => 5,
            'comment' => 'Excellent work!',
        ]);

        // Coupon
        Coupon::create([
            'code' => 'WELCOME10',
            'discount_percent' => 10,
            'valid_from' => now()->subDays(1)->toDateString(),
            'valid_to' => now()->addDays(30)->toDateString(),
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        // Review
        Review::create([
            'user_id' => $customer->id,
            'service_id' => $service->id,
            'rating' => 5,
            'comment' => 'Very satisfied with the service!',
        ]);

        // Location
        Location::create([
            'user_id' => $customer->id,
            'address' => 'Lusaka City Centre',
            'latitude' => -15.416667,
            'longitude' => 28.283333,
        ]);

        // Notification
        Notification::create([
            'user_id' => $fixerUser->id,
            'title' => 'New Job Assigned',
            'message' => 'You have a new service request scheduled for tomorrow.',
            'read' => false,
        ]);
        $this->command->info('OtherData seeded successfully.');
    }
}
