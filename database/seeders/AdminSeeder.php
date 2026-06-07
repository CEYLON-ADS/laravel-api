<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = config('ceylon.admin', []);

        $admins = [
            [
                'username' => $defaults['username'] ?? env('ADMIN_USERNAME', 'admin'),
                'password' => $defaults['password'] ?? env('ADMIN_PASSWORD', 'admin123'),
                'role' => 'super_admin',
            ],
            [
                'username' => 'manager',
                'password' => 'manager123',
                'role' => 'admin',
            ],
            [
                'username' => 'adsagent',
                'password' => 'agent123',
                'role' => 'ads_agent',
            ],
        ];

        foreach ($admins as $row) {
            AdminUser::query()->updateOrCreate(
                ['username' => $row['username']],
                [
                    'password' => Hash::make($row['password']),
                    'role' => $row['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
