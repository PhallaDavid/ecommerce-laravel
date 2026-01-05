<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Permissions
        $permissions = [
            // Users Module
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'View users list', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'description' => 'Create new users', 'module' => 'users'],
            ['name' => 'Update Users', 'slug' => 'update-users', 'description' => 'Update existing users', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'description' => 'Delete users', 'module' => 'users'],
            ['name' => 'Assign Roles', 'slug' => 'assign-roles', 'description' => 'Assign roles to users', 'module' => 'users'],
            ['name' => 'Revoke Roles', 'slug' => 'revoke-roles', 'description' => 'Revoke roles from users', 'module' => 'users'],

            // Roles Module
            ['name' => 'View Roles', 'slug' => 'view-roles', 'description' => 'View roles list', 'module' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'create-roles', 'description' => 'Create new roles', 'module' => 'roles'],
            ['name' => 'Update Roles', 'slug' => 'update-roles', 'description' => 'Update existing roles', 'module' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'delete-roles', 'description' => 'Delete roles', 'module' => 'roles'],
            ['name' => 'Assign Permissions', 'slug' => 'assign-permissions', 'description' => 'Assign permissions to roles', 'module' => 'roles'],
            ['name' => 'Revoke Permissions', 'slug' => 'revoke-permissions', 'description' => 'Revoke permissions from roles', 'module' => 'roles'],

            // Permissions Module
            ['name' => 'View Permissions', 'slug' => 'view-permissions', 'description' => 'View permissions list', 'module' => 'permissions'],
            ['name' => 'Create Permissions', 'slug' => 'create-permissions', 'description' => 'Create new permissions', 'module' => 'permissions'],
            ['name' => 'Update Permissions', 'slug' => 'update-permissions', 'description' => 'Update existing permissions', 'module' => 'permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'delete-permissions', 'description' => 'Delete permissions', 'module' => 'permissions'],

            // Products Module
            ['name' => 'View Products', 'slug' => 'view-products', 'description' => 'View products', 'module' => 'products'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'description' => 'Create products', 'module' => 'products'],
            ['name' => 'Update Products', 'slug' => 'update-products', 'description' => 'Update products', 'module' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'description' => 'Delete products', 'module' => 'products'],
            ['name' => 'Export Products', 'slug' => 'export-products', 'description' => 'Export products', 'module' => 'products'],

            // Orders Module
            ['name' => 'View Orders', 'slug' => 'view-orders', 'description' => 'View orders', 'module' => 'orders'],
            ['name' => 'Update Orders', 'slug' => 'update-orders', 'description' => 'Update orders', 'module' => 'orders'],
            ['name' => 'Delete Orders', 'slug' => 'delete-orders', 'description' => 'Delete orders', 'module' => 'orders'],
            ['name' => 'Export Orders', 'slug' => 'export-orders', 'description' => 'Export orders', 'module' => 'orders'],

            // Categories Module
            ['name' => 'View Categories', 'slug' => 'view-categories', 'description' => 'View categories', 'module' => 'categories'],
            ['name' => 'Create Categories', 'slug' => 'create-categories', 'description' => 'Create categories', 'module' => 'categories'],
            ['name' => 'Update Categories', 'slug' => 'update-categories', 'description' => 'Update categories', 'module' => 'categories'],
            ['name' => 'Delete Categories', 'slug' => 'delete-categories', 'description' => 'Delete categories', 'module' => 'categories'],

            // Dashboard Module
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'description' => 'View dashboard', 'module' => 'dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access',
                'is_active' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access',
                'is_active' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Management access',
                'is_active' => true,
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Content editing access',
                'is_active' => true,
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Basic user access',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Assign permissions based on role
            if ($role->slug === 'super-admin' || $role->slug === 'admin') {
                // Super Admin and Admin get all permissions
                $role->permissions()->sync(Permission::pluck('id')->toArray());
            } elseif ($role->slug === 'manager') {
                // Manager gets view, update, and export permissions
                $managerPermissions = Permission::whereIn('slug', [
                    'view-users', 'view-roles', 'view-permissions',
                    'view-products', 'update-products', 'export-products',
                    'view-orders', 'update-orders', 'export-orders',
                    'view-categories', 'update-categories',
                    'view-dashboard',
                ])->pluck('id')->toArray();
                $role->permissions()->sync($managerPermissions);
            } elseif ($role->slug === 'editor') {
                // Editor gets product and category management
                $editorPermissions = Permission::whereIn('slug', [
                    'view-products', 'create-products', 'update-products',
                    'view-categories', 'create-categories', 'update-categories',
                ])->pluck('id')->toArray();
                $role->permissions()->sync($editorPermissions);
            }
            // Customer role gets no special permissions (default user access)
        }

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
