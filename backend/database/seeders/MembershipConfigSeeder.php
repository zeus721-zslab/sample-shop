<?php

namespace Database\Seeders;

use App\Models\MembershipConfig;
use Illuminate\Database\Seeder;

class MembershipConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'grade'       => 'newbie',
                'min_amount'  => 0,
                'point_rate'  => 0.00,
                'description' => '신규 가입 등급 (적립 없음)',
            ],
            [
                'grade'       => 'silver',
                'min_amount'  => 300000,
                'point_rate'  => 1.50,
                'description' => '최근 12개월 30만원 이상 구매',
            ],
            [
                'grade'       => 'gold',
                'min_amount'  => 1000000,
                'point_rate'  => 2.00,
                'description' => '최근 12개월 100만원 이상 구매',
            ],
            [
                'grade'       => 'vip',
                'min_amount'  => 3000000,
                'point_rate'  => 3.00,
                'description' => '최근 12개월 300만원 이상 구매',
            ],
        ];

        foreach ($configs as $config) {
            MembershipConfig::updateOrCreate(['grade' => $config['grade']], $config);
        }
    }
}
