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
        'role' => 'Admin',
    ]);

    $response->assertRedirect(route('admin.teachers.index'));
    $this->assertDatabaseHas('users', ['name' => 'New Teacher', 'email' => 'new.teacher@example.com']);

    $teacher = User::query()->where('email', 'new.teacher@example.com')->firstOrFail();
    expect($teacher->hasRole('Teacher'))->toBeTrue();
    expect($teacher->hasRole('Admin'))->toBeFalse();
    expect(Hash::check('teacher-password', $teacher->password))->toBeTrue();
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
});

test('Admin can update teacher details and keep the existing password when the password is blank', function () {
    $admin = createTeacherModuleAdmin();
    $teacher = User::factory()->create(['email' => 'before@example.com']);
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
    ]);

    $response->assertRedirect(route('admin.teachers.index'));
    $this->assertDatabaseHas('users', ['id' => $teacher->id, 'name' => 'Updated Teacher', 'email' => 'after@example.com']);
    expect($teacher->fresh()->password)->toBe($currentPassword);
    expect($teacher->fresh()->hasRole('Teacher'))->toBeTrue();
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
