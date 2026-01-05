<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
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
                'role_slug' => 'super-admin',
            ],
            [
                'name' => 'Sale User',
                'email' => 'sale@gmail.com',
                'password' => '123456',
                'role' => 'sale',
                'role_slug' => 'manager',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'verify_status' => 'completed',
                ]
            );

            // Assign role using RBAC system
            if (isset($userData['role_slug'])) {
                $role = Role::where('slug', $userData['role_slug'])->first();
                if ($role) {
                    $user->assignRole($role);
                }
            }
        }

        $this->command->info('Admin & Sale users created successfully!');
        $this->command->warn('⚠️ Please change passwords after first login!');
    }
}

