<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'KINDLY10', 'type' => 'percent', 'value' => 10],
            ['code' => 'SAVE500', 'type' => 'fixed', 'value' => 500],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                array_merge($coupon, ['is_active' => true]),
            );
        }
    }
}
