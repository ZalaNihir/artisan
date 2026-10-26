<?php

use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function parentManagementUser(string $roleName): User
{
    $role = Role::findOrCreate($roleName, 'web');
    $permissions = collect(['parents.view', 'parents.create', 'parents.update', 'parents.delete'])
        ->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function studentForParentManagement(User $teacher, string $firstName): Student
{
    return Student::create([
        'first_name' => $firstName,
        'last_name' => 'Student',
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);
}

function validParentAttributes(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Alex',
        'last_name' => 'Morgan',
        'email' => 'family@example.com',
        'phone' => '5551234567',
        'alternate_phone' => '',
        'occupation' => 'Engineer',
        'address' => '12 School Street',
    ], $overrides);
}

it('creates a parent linked to multiple students with relationship details', function () {
    $teacher = parentManagementUser('Teacher');
    $firstStudent = studentForParentManagement($teacher, 'Avery');
    $secondStudent = studentForParentManagement($teacher, 'Jordan');

    $response = $this->actingAs($teacher)->post(route('parents.store'), [
        ...validParentAttributes(),
        'students' => [
            $firstStudent->id => ['selected' => '1', 'relationship' => 'Father'],
            $secondStudent->id => ['selected' => '1', 'relationship' => 'Father'],
        ],
    ]);

    $parent = StudentParent::query()->firstOrFail();

    $response->assertRedirect(route('parents.show', $parent));
    $this->assertDatabaseHas('parents', [
        'id' => $parent->id,
        'created_by' => $teacher->id,
        'email' => 'family@example.com',
    ]);
    $this->assertDatabaseHas('parent_student', [
        'parent_id' => $parent->id,
        'student_id' => $firstStudent->id,
        'relationship' => 'Father',
    ]);
    $this->assertDatabaseHas('parent_student', [
        'parent_id' => $parent->id,
        'student_id' => $secondStudent->id,
    ]);
});

it('reports missing required parent fields', function () {
    $teacher = parentManagementUser('Teacher');

    $response = $this->actingAs($teacher)->post(route('parents.store'), []);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'phone']);
    $this->assertDatabaseCount('parents', 0);
});

it('rejects parent fields that exceed their database limits', function () {
    $teacher = parentManagementUser('Teacher');

    $response = $this->actingAs($teacher)->post(route('parents.store'), validParentAttributes([
        'first_name' => str_repeat('a', 101),
        'phone' => str_repeat('1', 21),
    ]));

    $response->assertSessionHasErrors(['first_name', 'phone']);
    $this->assertDatabaseCount('parents', 0);
});

it('allows duplicate email and phone values for different parent records', function () {
    $admin = parentManagementUser('Admin');
    $attributes = validParentAttributes();

    $this->actingAs($admin)->post(route('parents.store'), $attributes)->assertRedirect();
    $this->actingAs($admin)->post(route('parents.store'), $attributes)->assertRedirect();

    $this->assertDatabaseCount('parents', 2);
});

it('lists and shows parents with their associated children', function () {
    $teacher = parentManagementUser('Teacher');
    $student = studentForParentManagement($teacher, 'Casey');
    $parent = StudentParent::factory()->create([
        'first_name' => 'Morgan',
        'last_name' => 'Test',
        'created_by' => $teacher->id,
    ]);
    $parent->students()->attach($student, ['relationship' => 'Guardian']);

    $this->actingAs($teacher)
        ->get(route('parents.create'))
        ->assertOk()
        ->assertSee('Casey Student');

    $this->get(route('parents.index'))
        ->assertOk()
        ->assertSee('Morgan')
        ->assertSee('1 child');

    $this->get(route('parents.show', $parent))
        ->assertOk()
        ->assertSee('Casey Student')
        ->assertSee('Guardian')
        ->assertDontSee('Primary contact');
});

it('updates parent details and synchronizes children without duplicate associations', function () {
    $teacher = parentManagementUser('Teacher');
    $student = studentForParentManagement($teacher, 'Taylor');
    $parent = StudentParent::factory()->create(['created_by' => $teacher->id]);
    $parent->students()->attach($student, ['relationship' => 'Mother']);
    $payload = [
        ...validParentAttributes(['phone' => '5557654321']),
        'students' => [$student->id => ['selected' => '1', 'relationship' => 'Mother']],
    ];

    $this->actingAs($teacher)->get(route('parents.edit', $parent))->assertOk()->assertSee('Taylor Student');

    $this->put(route('parents.update', $parent), $payload)->assertRedirect(route('parents.show', $parent));
    $this->put(route('parents.update', $parent), $payload)->assertRedirect(route('parents.show', $parent));

    $this->assertDatabaseHas('parents', ['id' => $parent->id, 'phone' => '5557654321']);
    $this->assertDatabaseCount('parent_student', 1);
});

it('rejects duplicate rows for the same parent and student association', function () {
    $teacher = parentManagementUser('Teacher');
    $student = studentForParentManagement($teacher, 'Jamie');
    $parent = StudentParent::factory()->create();
    $parent->students()->attach($student);

    expect(fn () => $parent->students()->attach($student))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('parent_student', 1);
});

it('lets admins manage all parents and associate students across teachers', function () {
    $admin = parentManagementUser('Admin');
    $teacher = parentManagementUser('Teacher');
    $student = studentForParentManagement($teacher, 'Riley');

    $response = $this->actingAs($admin)->post(route('parents.store'), [
        ...validParentAttributes(),
        'students' => [$student->id => ['selected' => '1', 'relationship' => 'Mother']],
    ]);

    $parent = StudentParent::query()->firstOrFail();
    $response->assertRedirect(route('parents.show', $parent));
    $this->assertDatabaseHas('parent_student', ['parent_id' => $parent->id, 'student_id' => $student->id]);

    $this->get(route('parents.index'))->assertOk()->assertSee('Morgan');
});

it('hides parent records and student associations outside a teachers scope', function () {
    $teacher = parentManagementUser('Teacher');
    $otherTeacher = parentManagementUser('Teacher');
    $otherStudent = studentForParentManagement($otherTeacher, 'Outside');
    $parent = StudentParent::factory()->create();
    $parent->students()->attach($otherStudent);

    $this->actingAs($teacher)
        ->get(route('parents.show', $parent))
        ->assertNotFound();

    $response = $this->actingAs($teacher)->post(route('parents.store'), [
        ...validParentAttributes(),
        'students' => [$otherStudent->id => ['selected' => '1']],
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('parents', 1);
});

it('soft deletes a parent without deleting the student or its historical association', function () {
    $teacher = parentManagementUser('Teacher');
    $student = studentForParentManagement($teacher, 'Sam');
    $parent = StudentParent::factory()->create(['created_by' => $teacher->id]);
    $parent->students()->attach($student, ['relationship' => 'Guardian']);

    $response = $this->actingAs($teacher)->delete(route('parents.destroy', $parent));

    $response->assertRedirect(route('parents.index'));
    $this->assertSoftDeleted($parent);
    $this->assertModelExists($student);
    $this->assertDatabaseHas('parent_student', ['parent_id' => $parent->id, 'student_id' => $student->id]);
});

it('returns not found for an unknown parent and redirects guests to login', function () {
    $teacher = parentManagementUser('Teacher');

    $this->actingAs($teacher)->get(route('parents.show', 999999))->assertNotFound();

    auth()->logout();
    $this->get(route('parents.index'))->assertRedirect(route('login'));
});

it('forbids authenticated users without a parent role or permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('parents.index'))->assertForbidden();
});
