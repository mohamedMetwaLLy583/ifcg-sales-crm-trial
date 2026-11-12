<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // إنشاء الأدمن
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('123456789'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // إنشاء 50 عميل مختلف
        User::factory()
            ->count(50)
            ->create()
            ->each(function ($user) {
                $user->assignRole('customer');
            });
    }
}
