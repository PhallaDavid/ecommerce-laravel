<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => '123456',
                'role' => 'admin',
            ],
            [
                'name' => 'Sale User',
                'email' => 'sale@gmail.com',
                'password' => '123456',
                'role' => 'sale',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                    'verify_status' => 'completed',
                ]
            );
        }

        $this->command->info('Admin & Sale users created successfully!');
        $this->command->warn('⚠️ Please change passwords after first login!');
    }
}
