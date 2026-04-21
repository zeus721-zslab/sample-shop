<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('DEMO_EMAIL', 'demo@zslab.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => '데모 계정',
                'password'  => Hash::make(env('DEMO_PASSWORD', 'demo1234!')),
                'role'      => 'demo',
                'is_active' => true,
            ]
        );

        $this->command->info("Demo user created: {$email}");
    }
}
