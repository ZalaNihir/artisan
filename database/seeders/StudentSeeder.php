<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use RuntimeException;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::query()
            ->role('Teacher')
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'Admin'))
            ->get();

        if ($teachers->isEmpty()) {
            throw new RuntimeException('Seed teachers before seeding students.');
        }

        $households = [
            [
                'students' => [
                    [
                        'first_name' => 'Ella',
                        'last_name' => 'Morgan',
                        'grade' => 'Grade 5',
                        'email' => 'ella.morgan@school.example.test',
                        'phone_number' => '+1-555-0101',
                        'dob' => '2014-04-12',
                        'gender' => 'female',
                        'address' => '18 Cedar Lane',
                    ],
                    [
                        'first_name' => 'Noah',
                        'last_name' => 'Morgan',
                        'grade' => 'Grade 3',
                        'email' => 'noah.morgan@school.example.test',
                        'phone_number' => '+1-555-0102',
                        'dob' => '2016-09-03',
                        'gender' => 'male',
                        'address' => '18 Cedar Lane',
                    ],
                ],
            ],
            [
                'students' => [
                    [
                        'first_name' => 'Ava',
                        'last_name' => 'Chen',
                        'grade' => 'Grade 7',
                        'email' => 'ava.chen@school.example.test',
                        'phone_number' => '+1-555-0103',
                        'dob' => '2013-11-21',
                        'gender' => 'female',
                        'address' => '42 Park Road',
                    ],
                    [
                        'first_name' => 'Liam',
                        'last_name' => 'Chen',
                        'grade' => 'Grade 5',
                        'email' => 'liam.chen@school.example.test',
                        'phone_number' => '+1-555-0104',
                        'dob' => '2015-06-08',
                        'gender' => 'male',
                        'address' => '42 Park Road',
                    ],
                ],
            ],
            [
                'students' => [
                    [
                        'first_name' => 'Harper',
                        'last_name' => 'Williams',
                        'grade' => 'Grade 6',
                        'email' => 'harper.williams@school.example.test',
                        'phone_number' => '+1-555-0105',
                        'dob' => '2014-02-17',
                        'gender' => 'female',
                        'address' => '7 Lake Street',
                    ],
                    [
                        'first_name' => 'Mason',
                        'last_name' => 'Williams',
                        'grade' => 'Grade 4',
                        'email' => 'mason.williams@school.example.test',
                        'phone_number' => '+1-555-0106',
                        'dob' => '2016-12-29',
                        'gender' => 'male',
                        'address' => '7 Lake Street',
                    ],
                ],
            ],
        ];

        foreach ($households as $householdIndex => $household) {
            $teacher = $teachers[$householdIndex % $teachers->count()];

            foreach ($household['students'] as $student) {
                Student::query()->firstOrCreate(
                    ['email' => $student['email']],
                    [...$student, 'teacher_id' => $teacher->getKey(), 'status' => 'active']
                );
            }
        }
    }
}
