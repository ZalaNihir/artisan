<?php

use App\Jobs\SendAnnouncementNotificationJob;
use App\Jobs\SendTeacherAnnouncementEmailsJob;
use App\Models\Announcement;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Notifications\TeacherAnnouncementEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function teacherForAnnouncements(): User
{
    $role = Role::findOrCreate('Teacher', 'web');
    $permissions = collect(['announcements.view', 'announcements.create'])
        ->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    $role->syncPermissions($permissions);

    $teacher = User::factory()->create();
    $teacher->assignRole($role);

    return $teacher;
}

function adminForAnnouncements(): User
{
    $role = Role::findOrCreate('Admin', 'web');
    $permissions = collect(['announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete'])
        ->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    $role->syncPermissions($permissions);

    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function studentForAnnouncements(User $teacher, string $email): Student
{
    return Student::create([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => $email,
        'teacher_id' => $teacher->id,
        'status' => 'active',
    ]);
}

function parentForAnnouncements(string $email): StudentParent
{
    return StudentParent::create([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => $email,
        'phone' => fake()->numerify('##########'),
    ]);
}

function teacherAnnouncementAttributes(array $overrides = []): array
{
    return array_merge([
        'title' => 'Field trip update',
        'message' => 'Please review the updated schedule.',
        'audience' => 'both',
    ], $overrides);
}

it('creates a teacher announcement and queues email delivery', function () {
    Queue::fake();
    $teacher = teacherForAnnouncements();

    $this->actingAs($teacher)->get(route('teacher.announcements.index'))->assertOk();
    $this->get(route('teacher.announcements.create'))->assertOk();

    $response = $this->actingAs($teacher)->post(
        route('teacher.announcements.store'),
        teacherAnnouncementAttributes(),
    );

    $announcement = Announcement::query()->firstOrFail();

    $response->assertRedirect(route('teacher.announcements.index'));
    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'created_by' => $teacher->id,
        'audience' => 'both',
    ]);
    Queue::assertPushed(SendTeacherAnnouncementEmailsJob::class, fn (SendTeacherAnnouncementEmailsJob $job): bool => $job->announcement->is($announcement));
    Queue::assertNotPushed(SendAnnouncementNotificationJob::class);
});

it('validates the teacher announcement audience and message', function () {
    Queue::fake();
    $teacher = teacherForAnnouncements();

    $response = $this->actingAs($teacher)->post(route('teacher.announcements.store'), [
        'title' => 'Important update',
        'message' => str_repeat('a', 10001),
        'audience' => 'teachers',
    ]);

    $response->assertSessionHasErrors(['message', 'audience']);
    $this->assertDatabaseCount('announcements', 0);
    Queue::assertNotPushed(SendTeacherAnnouncementEmailsJob::class);
});

it('emails both audiences once per unique address and only for the creating teacher', function () {
    Notification::fake();
    $teacher = teacherForAnnouncements();
    $otherTeacher = teacherForAnnouncements();
    $studentWithSharedEmail = studentForAnnouncements($teacher, 'shared@example.test');
    $studentWithOwnEmail = studentForAnnouncements($teacher, 'student@example.test');
    $inactiveStudent = studentForAnnouncements($teacher, 'inactive@example.test');
    $inactiveStudent->update(['status' => 'inactive']);
    $otherTeachersStudent = studentForAnnouncements($otherTeacher, 'other@example.test');

    $sharedParent = parentForAnnouncements('shared@example.test');
    $sharedParent->students()->attach($studentWithSharedEmail);
    $sharedParent->students()->attach($studentWithOwnEmail);

    $secondParent = parentForAnnouncements('parent@example.test');
    $secondParent->students()->attach($studentWithSharedEmail);

    $inactiveParent = parentForAnnouncements('inactive-parent@example.test');
    $inactiveParent->students()->attach($inactiveStudent);

    $otherParent = parentForAnnouncements('other-parent@example.test');
    $otherParent->students()->attach($otherTeachersStudent);

    $announcement = Announcement::create([
        ...teacherAnnouncementAttributes(),
        'created_by' => $teacher->id,
    ]);

    (new SendTeacherAnnouncementEmailsJob($announcement))->handle();

    Notification::assertSentOnDemandTimes(TeacherAnnouncementEmailNotification::class, 3);
    foreach (['shared@example.test', 'student@example.test', 'parent@example.test'] as $email) {
        Notification::assertSentOnDemand(
            TeacherAnnouncementEmailNotification::class,
            fn (TeacherAnnouncementEmailNotification $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === $email
                && $channels === ['mail']
                && $notification->title === 'Field trip update',
        );
    }
});

it('emails only parent records when the audience is parents', function () {
    Notification::fake();
    $teacher = teacherForAnnouncements();
    $student = studentForAnnouncements($teacher, 'student@example.test');
    $parent = parentForAnnouncements('parent@example.test');
    $parent->students()->attach($student);

    $announcement = Announcement::create([
        ...teacherAnnouncementAttributes(['audience' => 'parents']),
        'created_by' => $teacher->id,
    ]);

    (new SendTeacherAnnouncementEmailsJob($announcement))->handle();

    Notification::assertSentOnDemandTimes(TeacherAnnouncementEmailNotification::class, 1);
    Notification::assertSentOnDemand(
        TeacherAnnouncementEmailNotification::class,
        fn (TeacherAnnouncementEmailNotification $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'parent@example.test',
    );
});

it('does not email student addresses when the audience is students', function () {
    Notification::fake();
    $teacher = teacherForAnnouncements();
    $student = studentForAnnouncements($teacher, 'student@example.test');
    $parent = parentForAnnouncements('parent@example.test');
    $parent->students()->attach($student);

    $announcement = Announcement::create([
        ...teacherAnnouncementAttributes(['audience' => 'students']),
        'created_by' => $teacher->id,
    ]);

    (new SendTeacherAnnouncementEmailsJob($announcement))->handle();

    Notification::assertSentOnDemandTimes(TeacherAnnouncementEmailNotification::class, 1);
    Notification::assertSentOnDemand(
        TeacherAnnouncementEmailNotification::class,
        fn (TeacherAnnouncementEmailNotification $notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'student@example.test',
    );
});

it('keeps admin announcements targeted to teachers and off the email job', function () {
    Queue::fake();
    $admin = adminForAnnouncements();

    $response = $this->actingAs($admin)->post(route('admin.announcements.store'), [
        'title' => 'Staff meeting',
        'message' => 'Meet in the library.',
    ]);

    $announcement = Announcement::query()->firstOrFail();

    $response->assertRedirect(route('admin.announcements.index'));
    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'created_by' => $admin->id,
        'audience' => 'teachers',
    ]);
    Queue::assertPushed(SendAnnouncementNotificationJob::class);
    Queue::assertNotPushed(SendTeacherAnnouncementEmailsJob::class);
    expect((new AnnouncementNotification($announcement->id, $announcement->title, $announcement->message))->via($admin))
        ->toBe(['database']);
});

it('shows teacher-created announcements in the admin announcement list', function () {
    Queue::fake();
    $teacher = teacherForAnnouncements();
    $admin = adminForAnnouncements();

    $this->actingAs($teacher)->post(route('teacher.announcements.store'), teacherAnnouncementAttributes())
        ->assertRedirect(route('teacher.announcements.index'));

    $this->actingAs($admin)
        ->get(route('admin.announcements.index'))
        ->assertOk()
        ->assertSee('Field trip update')
        ->assertSee('Students and parents');
});

it('forbids users without the Teacher role from teacher announcement routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('teacher.announcements.index'))
        ->assertForbidden();

    $admin = adminForAnnouncements();
    $this->actingAs($admin)
        ->get(route('teacher.announcements.index'))
        ->assertForbidden();
});
