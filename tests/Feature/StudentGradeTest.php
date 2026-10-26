<?php

use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admins must provide a grade when creating a student', function () {
    ['admin' => $admin, 'teacher' => $teacher] = createStudentGradeActors();
    $studentData = studentGradePayload($teacher);

    $this->actingAs($admin)
        ->post(route('students.store'), $studentData)
        ->assertSessionHasErrors('grade');

    $studentData['grade'] = 'Grade 5';

    $response = $this->post(route('students.store'), $studentData);

    $response->assertRedirect();
    $student = Student::query()->where('email', $studentData['email'])->firstOrFail();

    expect($student->grade)->toBe('Grade 5');

    $this->get(route('students.show', $student))->assertOk()->assertSee('Grade 5');
});

test('admins must provide a grade when updating a student', function () {
    ['admin' => $admin, 'teacher' => $teacher] = createStudentGradeActors();
    $student = Student::create([
        'first_name' => 'Legacy',
        'last_name' => 'Student',
        'grade' => 'Grade 2',
        'email' => 'legacy.student@example.test',
        'dob' => '2016-05-12',
        'gender' => 'female',
        'address' => '12 Main Street',
        'teacher_id' => $teacher->id,
    ]);
    $studentData = studentGradePayload($teacher);
    $studentData['first_name'] = 'Updated';
    $studentData['email'] = $student->email;
    unset($studentData['grade']);

    $this->actingAs($admin)
        ->put(route('students.update', $student), $studentData)
        ->assertSessionHasErrors('grade');

    $studentData['grade'] = 'Grade 3';

    $this->put(route('students.update', $student), $studentData)
        ->assertRedirect(route('students.show', $student));

    expect($student->fresh()->grade)->toBe('Grade 3');
});

/**
 * @return array{admin: User, teacher: User}
 */
function createStudentGradeActors(): array
{
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');

    return ['admin' => $admin, 'teacher' => $teacher];
}

/** @return array<string, mixed> */
function studentGradePayload(User $teacher): array
{
    return [
        'first_name' => 'Casey',
        'last_name' => 'Taylor',
        'email' => 'casey.taylor@example.test',
        'phone_number' => '+1-555-0134',
        'dob' => '2015-09-14',
        'gender' => 'female',
        'address' => '24 School Road',
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ];
}
