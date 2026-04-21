<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@zslab.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => 'zslab 관리자',
                'password'  => Hash::make(env('ADMIN_PASSWORD', 'change_me_password')),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info("Admin user created: {$email}");
    }
}
