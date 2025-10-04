<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing coupons while respecting FK constraints
        Coupon::query()->delete();

        $today = now()->toDateString();
        $in60 = now()->addDays(60)->toDateString();

        // Zambia‑tailored examples
        Coupon::create([
            'code' => 'WELCOME10',
            'title' => 'Chi 10% muli imwe',
            'description' => 'Get a welcome discount offer by using the code WELCOME10',
            'discount_percent' => 10,
            'discount_amount' => null,
            'valid_from' => $today,
            'valid_to' => $in60,
            'usage_limit' => 1000,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'WEEKEND50',
            'title' => 'Weekend Special',
            'description' => 'Save ZMW 50 on weekend bookings (Fri–Sun).',
            'discount_percent' => 0,
            'discount_amount' => 50,
            'valid_from' => $today,
            'valid_to' => $in60,
            'usage_limit' => 500,
            'used_count' => 0,
        ]);

        Coupon::create([
            'code' => 'FIXITZED20',
            'title' => 'September Madoda',
            'description' => '20% off labour for first‑time customers in Lusaka & Copperbelt.',
            'discount_percent' => 20,
            'discount_amount' => null,
            'valid_from' => $today,
            'valid_to' => $in60,
            'usage_limit' => 300,
            'used_count' => 0,
        ]);
    }
}
