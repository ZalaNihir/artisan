<?php

use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permissions = collect([
        'teachers.view',
        'students.view',
        'parents.view',
        'announcements.view',
    ])->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    Role::findOrCreate('Admin', 'web')->syncPermissions($permissions);
    Role::findOrCreate('Teacher', 'web')->syncPermissions($permissions->reject(fn (Permission $permission) => $permission->name === 'teachers.view'));
});

it('shows system-wide totals to admins and links to the existing listings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('Teacher');
    createStudentFor($teacher);
    createStudentFor($otherTeacher);
    StudentParent::factory()->create();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewHas('counts', [
            'teachers' => 2,
            'students' => 2,
            'parents' => 1,
            'unread_notifications' => 0,
        ])
        ->assertSee('Total Teachers')
        ->assertSee('Total Students')
        ->assertSee('Total Parents')
        ->assertSee(route('admin.teachers.index'), false)
        ->assertSee(route('students.index'), false)
        ->assertSee(route('parents.index'), false);
});

it('limits teacher totals and existing listing links to their students and associated parents', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('Teacher');

    $studentOne = createStudentFor($teacher, 'Owned');
    $studentTwo = createStudentFor($teacher, 'Also owned');
    $otherStudent = createStudentFor($otherTeacher, 'Not owned');
    $sharedParent = StudentParent::factory()->create(['first_name' => 'Scoped Parent']);
    $otherParent = StudentParent::factory()->create(['first_name' => 'Other Parent']);
    $sharedParent->students()->attach([$studentOne->id, $studentTwo->id]);
    $otherParent->students()->attach($otherStudent->id);

    $dashboard = $this->actingAs($teacher)->get(route('dashboard'));
    $dashboard->assertOk()
        ->assertViewHas('counts', [
            'teachers' => 0,
            'students' => 2,
            'parents' => 1,
            'unread_notifications' => 0,
        ])
        ->assertSee('My Students')
        ->assertSee('My Parents')
        ->assertSee(route('students.index'), false)
        ->assertSee(route('parents.index'), false);

    $this->get(route('students.index').'?teacher_id='.$otherTeacher->id)
        ->assertOk()
        ->assertSee('Owned')
        ->assertDontSee('Not owned');

    $this->get(route('parents.index'))
        ->assertOk()
        ->assertSee('Scoped Parent')
        ->assertDontSee('Other Parent');
});

it('counts unread teacher announcements using each teachers notification read state', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');
    $otherTeacher = User::factory()->create();
    $otherTeacher->assignRole('Teacher');

    $teacher->notify(new AnnouncementNotification(51, 'First update', 'First message'));
    $teacher->notifications()->firstOrFail()->markAsRead();
    $teacher->notify(new AnnouncementNotification(52, 'Second update', 'Second message'));
    $otherTeacher->notify(new AnnouncementNotification(53, 'Another update', 'Another message'));

    $this->actingAs($teacher)->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('counts.unread_notifications', 1)
        ->assertSee('Unread Notifications')
        ->assertSee(route('teacher.notifications.index'), false);
});

function createStudentFor(User $teacher, string $name = 'Student'): Student
{
    return Student::create([
        'first_name' => $name,
        'last_name' => 'Test',
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);
}
