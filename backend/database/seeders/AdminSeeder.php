<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@zslab.com'],
            [
                'name'      => 'zslab 관리자',
                'password'  => Hash::make('zslab@admin2026!'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Admin user created: admin@zslab.com / zslab@admin2026!');
    }
}
