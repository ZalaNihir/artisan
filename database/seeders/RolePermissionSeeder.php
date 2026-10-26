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

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('Admin', 'web');
        $teacherRole = Role::findOrCreate('Teacher', 'web');

        $adminRole->syncPermissions($permissions);

        $teacherRole->syncPermissions([
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
        ]);

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
