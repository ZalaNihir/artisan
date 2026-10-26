<?php

namespace Database\Seeders;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacherRole = Role::findOrCreate('Teacher', 'web');

        $teachers = TeacherFactory::new()->count(10)->create();

        $teachers->each(function ($user) use ($teacherRole) {
            $user->assignRole($teacherRole);
        });
    }
}
