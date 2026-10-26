<?php

use App\Models\User;
use App\Services\TeacherService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

function createTeacherModuleAdmin(): User
{
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

test('guests are redirected from teacher management', function () {
    $this->get(route('admin.teachers.index'))
        ->assertRedirect(route('login'));
});

test('Admin can list teachers and create a Teacher account without assigning a submitted role', function () {
    $admin = createTeacherModuleAdmin();
    $existingTeacher = User::factory()->create(['name' => 'Existing Teacher']);
    $existingTeacher->assignRole('Teacher');
    Mail::fake();

    $this->actingAs($admin)->get(route('admin.teachers.create'))->assertOk();

    $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'name' => 'New Teacher',
        'email' => 'new.teacher@example.com',
        'password' => 'teacher-password',
        'password_confirmation' => 'teacher-password',
        'employee_id' => 'EMP-2026-001',
        'phone' => '+1 415 555 0132',
        'address' => '14 School Lane',
        'qualification' => 'M.Ed.',
        'joining_date' => '2026-01-15',
        'role' => 'Admin',
    ]);

    $response->assertRedirect(route('admin.teachers.index'));
    $this->assertDatabaseHas('users', ['name' => 'New Teacher', 'email' => 'new.teacher@example.com']);

    $teacher = User::query()->where('email', 'new.teacher@example.com')->firstOrFail();
    expect($teacher->hasRole('Teacher'))->toBeTrue();
    expect($teacher->hasRole('Admin'))->toBeFalse();
    expect(Hash::check('teacher-password', $teacher->password))->toBeTrue();
    expect($teacher->employee_id)->toBe('EMP-2026-001');
    expect($teacher->phone)->toBe('+1 415 555 0132');
    expect($teacher->address)->toBe('14 School Lane');
    expect($teacher->qualification)->toBe('M.Ed.');
    expect($teacher->joining_date)->toBe('2026-01-15');
    Mail::assertNothingOutgoing();

    $this->get(route('admin.teachers.index'))
        ->assertOk()
        ->assertSee('Existing Teacher')
        ->assertSee('New Teacher');
});

test('teacher accounts can authenticate through Fortify after creation', function () {
    createTeacherModuleAdmin();

    $teacher = app(TeacherService::class)->create([
        'name' => 'Login Teacher',
        'email' => 'login.teacher@example.com',
        'password' => 'teacher-password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $teacher->email,
        'password' => 'teacher-password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($teacher);
});

test('teacher creation safely creates the Teacher role when it does not exist', function () {
    Role::query()->delete();

    $teacher = app(TeacherService::class)->create([
        'name' => 'First Teacher',
        'email' => 'first.teacher@example.com',
        'password' => 'teacher-password',
    ]);

    expect(Role::query()->where('name', 'Teacher')->count())->toBe(1);
    expect($teacher->hasRole('Teacher'))->toBeTrue();
    expect($teacher->employee_id)->toBeNull();
    expect($teacher->phone)->toBeNull();
    expect($teacher->address)->toBeNull();
    expect($teacher->qualification)->toBeNull();
    expect($teacher->joining_date)->toBeNull();
});

test('Admin can update teacher details and keep the existing password when the password is blank', function () {
    $admin = createTeacherModuleAdmin();
    $teacher = User::factory()->create(['email' => 'before@example.com', 'employee_id' => 'EMP-EDIT-01']);
    $teacher->assignRole('Teacher');
    $currentPassword = $teacher->password;

    $this->actingAs($admin)
        ->get(route('admin.teachers.edit', $teacher))
        ->assertOk()
        ->assertSee('before@example.com');

    $response = $this->actingAs($admin)->put(route('admin.teachers.update', $teacher), [
        'name' => 'Updated Teacher',
        'email' => 'after@example.com',
        'password' => '',
        'password_confirmation' => '',
        'employee_id' => 'EMP-EDIT-01',
        'phone' => '+44 20 7946 0958',
        'address' => 'Updated address',
        'qualification' => 'M.Sc. Mathematics',
        'joining_date' => '2024-08-01',
    ]);

    $response->assertRedirect(route('admin.teachers.index'));
    $this->assertDatabaseHas('users', ['id' => $teacher->id, 'name' => 'Updated Teacher', 'email' => 'after@example.com']);
    expect($teacher->fresh()->password)->toBe($currentPassword);
    expect($teacher->fresh()->hasRole('Teacher'))->toBeTrue();
    expect($teacher->fresh()->employee_id)->toBe('EMP-EDIT-01');
    expect($teacher->fresh()->phone)->toBe('+44 20 7946 0958');
    expect($teacher->fresh()->address)->toBe('Updated address');
    expect($teacher->fresh()->qualification)->toBe('M.Sc. Mathematics');
    expect($teacher->fresh()->joining_date)->toBe('2024-08-01');
});

test('Admin can delete teachers but cannot modify or delete Admin accounts', function () {
    $admin = createTeacherModuleAdmin();
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');

    $this->actingAs($admin)
        ->delete(route('admin.teachers.destroy', $teacher))
        ->assertRedirect(route('admin.teachers.index'));

    $this->assertDatabaseMissing('users', ['id' => $teacher->id]);

    $this->put(route('admin.teachers.update', $admin), [
        'name' => 'Changed Admin',
        'email' => $admin->email,
        'password' => '',
        'password_confirmation' => '',
    ])->assertForbidden();

    $this->delete(route('admin.teachers.destroy', $admin))->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => $admin->name]);
});

test('non Admin users cannot access teacher management routes', function () {
    createTeacherModuleAdmin();
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');

    $this->actingAs($teacher)
        ->get(route('admin.teachers.index'))
        ->assertForbidden();
});

test('duplicate teacher email addresses are rejected', function () {
    $admin = createTeacherModuleAdmin();
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'name' => 'Duplicate Teacher',
        'email' => 'taken@example.com',
        'password' => 'teacher-password',
        'password_confirmation' => 'teacher-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseMissing('users', ['name' => 'Duplicate Teacher']);
});

test('duplicate employee IDs are rejected without creating another teacher', function () {
    $admin = createTeacherModuleAdmin();
    User::factory()->create(['employee_id' => 'EMP-TAKEN']);
    $targetTeacher = User::factory()->create();
    $targetTeacher->assignRole('Teacher');

    $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'name' => 'Duplicate Employee ID',
        'email' => 'duplicate.employee@example.com',
        'password' => 'teacher-password',
        'password_confirmation' => 'teacher-password',
        'employee_id' => 'EMP-TAKEN',
    ])->assertSessionHasErrors('employee_id');

    $this->assertDatabaseMissing('users', ['email' => 'duplicate.employee@example.com']);

    $this->actingAs($admin)->put(route('admin.teachers.update', $targetTeacher), [
        'name' => $targetTeacher->name,
        'email' => $targetTeacher->email,
        'password' => '',
        'password_confirmation' => '',
        'employee_id' => 'EMP-TAKEN',
    ])->assertSessionHasErrors('employee_id');
});

test('invalid teacher profile fields are rejected', function (string $field, mixed $value) {
    $admin = createTeacherModuleAdmin();

    $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'name' => 'Invalid Profile Teacher',
        'email' => 'invalid.profile@example.com',
        'password' => 'teacher-password',
        'password_confirmation' => 'teacher-password',
        $field => $value,
    ])->assertSessionHasErrors($field);
})->with([
    'phone exceeds the supported length' => ['phone', str_repeat('1', 31)],
    'address exceeds the supported length' => ['address', str_repeat('a', 16001)],
    'qualification exceeds the supported length' => ['qualification', str_repeat('a', 256)],
    'joining date is invalid' => ['joining_date', 'not-a-date'],
]);

test('empty employee IDs are normalized to null', function () {
    $admin = createTeacherModuleAdmin();

    $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'name' => 'Teacher Without Employee ID',
        'email' => 'no.employee.id@example.com',
        'password' => 'teacher-password',
        'password_confirmation' => 'teacher-password',
        'employee_id' => '',
    ])->assertRedirect(route('admin.teachers.index'));

    $teacher = User::query()->where('email', 'no.employee.id@example.com')->firstOrFail();
    expect($teacher->employee_id)->toBeNull();
});
