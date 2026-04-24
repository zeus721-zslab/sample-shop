<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'             => 'WELCOME10',
                'name'             => '신규가입 10% 할인',
                'type'             => 'percent',
                'value'            => 10,
                'min_order_amount' => 10000,
                'max_discount_amount' => 5000,
                'max_uses'         => null,
                'is_active'        => true,
                'expires_at'       => now()->addYear(),
            ],
            [
                'code'             => 'SAVE5000',
                'name'             => '5,000원 즉시 할인',
                'type'             => 'fixed',
                'value'            => 5000,
                'min_order_amount' => 30000,
                'max_discount_amount' => null,
                'max_uses'         => 100,
                'is_active'        => true,
                'expires_at'       => now()->addMonths(3),
            ],
            [
                'code'             => 'SUMMER20',
                'name'             => '여름 시즌 20% 할인',
                'type'             => 'percent',
                'value'            => 20,
                'min_order_amount' => 50000,
                'max_discount_amount' => 20000,
                'max_uses'         => 50,
                'is_active'        => true,
                'expires_at'       => now()->addMonths(2),
            ],
            [
                'code'             => 'VIP3000',
                'name'             => 'VIP 전용 3,000원 할인',
                'type'             => 'fixed',
                'value'            => 3000,
                'min_order_amount' => 0,
                'max_discount_amount' => null,
                'max_uses'         => null,
                'is_active'        => true,
                'expires_at'       => now()->addYear(),
            ],
        ];

        foreach ($coupons as $data) {
            Coupon::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
