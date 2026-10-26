<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentParent;
use Illuminate\Database\Seeder;
use RuntimeException;

class StudentParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $households = [
            [
                'student_emails' => [
                    'ella.morgan@school.example.test',
                    'noah.morgan@school.example.test',
                ],
                'address' => '18 Cedar Lane',
                'parents' => [
                    [
                        'first_name' => 'Jordan',
                        'last_name' => 'Morgan',
                        'email' => 'jordan.morgan@school.example.test',
                        'phone' => '+1-555-0201',
                        'occupation' => 'Nurse',
                        'relationship' => 'Father',
                        'is_primary' => true,
                    ],
                    [
                        'first_name' => 'Taylor',
                        'last_name' => 'Morgan',
                        'email' => 'taylor.morgan@school.example.test',
                        'phone' => '+1-555-0202',
                        'occupation' => 'Teacher',
                        'relationship' => 'Mother',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'student_emails' => [
                    'ava.chen@school.example.test',
                    'liam.chen@school.example.test',
                ],
                'address' => '42 Park Road',
                'parents' => [
                    [
                        'first_name' => 'Mei',
                        'last_name' => 'Chen',
                        'email' => 'mei.chen@school.example.test',
                        'phone' => '+1-555-0203',
                        'occupation' => 'Accountant',
                        'relationship' => 'Mother',
                        'is_primary' => true,
                    ],
                    [
                        'first_name' => 'David',
                        'last_name' => 'Chen',
                        'email' => 'david.chen@school.example.test',
                        'phone' => '+1-555-0204',
                        'occupation' => 'Engineer',
                        'relationship' => 'Father',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'student_emails' => [
                    'harper.williams@school.example.test',
                    'mason.williams@school.example.test',
                ],
                'address' => '7 Lake Street',
                'parents' => [
                    [
                        'first_name' => 'Morgan',
                        'last_name' => 'Williams',
                        'email' => 'morgan.williams@school.example.test',
                        'phone' => '+1-555-0205',
                        'occupation' => 'Small business owner',
                        'relationship' => 'Guardian',
                        'is_primary' => true,
                    ],
                ],
            ],
        ];

        $studentEmails = collect($households)->flatMap(fn (array $household): array => $household['student_emails']);
        $students = Student::query()->whereIn('email', $studentEmails)->get()->keyBy('email');

        if ($students->count() !== $studentEmails->count()) {
            throw new RuntimeException('Seed students before seeding their parents.');
        }

        foreach ($households as $household) {
            $children = collect($household['student_emails'])
                ->map(fn (string $email): Student => $students->get($email));

            foreach ($household['parents'] as $parentAttributes) {
                $parent = StudentParent::query()->firstOrCreate(
                    ['email' => $parentAttributes['email']],
                    [
                        'first_name' => $parentAttributes['first_name'],
                        'last_name' => $parentAttributes['last_name'],
                        'phone' => $parentAttributes['phone'],
                        'occupation' => $parentAttributes['occupation'],
                        'address' => $household['address'],
                    ]
                );

                $parent->students()->syncWithoutDetaching(
                    $children->mapWithKeys(fn (Student $student): array => [
                        $student->getKey() => [
                            'relationship' => $parentAttributes['relationship'],
                            'is_primary' => $parentAttributes['is_primary'],
                        ],
                    ])->all()
                );
            }
        }
    }
}
