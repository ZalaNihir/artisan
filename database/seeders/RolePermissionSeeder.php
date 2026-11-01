<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.delete',

            'students.view',
            'students.create',
            'students.update',
            'students.delete',

            'parents.view',
            'parents.create',
            'parents.update',
            'parents.delete',

            'announcements.view',
            'announcements.create',
            'announcements.update',
            'announcements.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $permissionModels = Permission::whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->get();

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $teacherRole = Role::firstOrCreate([
            'name' => 'Teacher',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions($permissionModels);

        $teacherRole->syncPermissions(
            $permissionModels->whereIn('name', [
                'students.view',
                'students.create',
                'students.update',
                'students.delete',

                'parents.view',
                'parents.create',
                'parents.update',
                'parents.delete',

                'announcements.view',
                'announcements.create',
            ])
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'School Admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles([$adminRole]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
