<?php

use App\Jobs\SendAnnouncementNotificationJob;
use App\Jobs\SendTeacherAnnouncementEmailsJob;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $permission = Permission::findOrCreate('announcements.view', 'web');
    $createPermission = Permission::findOrCreate('announcements.create', 'web');
    Role::findOrCreate('Teacher', 'web')->givePermissionTo([$permission, $createPermission]);
    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Student', 'web');
});

test('teachers can list their admin notifications and see the unread count on dashboard', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');
    $teacher->notify(new AnnouncementNotification(42, 'School update', 'The school will open at 8 AM.'));

    $this->actingAs($teacher)
        ->get(route('teacher.notifications.index'))
        ->assertOk()
        ->assertSee('School update')
        ->assertSee('The school will open at 8 AM.')
        ->assertSee('Unread');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('1 unread');
});

test('opening a notification marks only that teachers notification as read', function () {
    $firstTeacher = User::factory()->create();
    $firstTeacher->assignRole('Teacher');
    $secondTeacher = User::factory()->create();
    $secondTeacher->assignRole('Teacher');

    Notification::send([$firstTeacher, $secondTeacher], new AnnouncementNotification(91, 'Term dates', 'The new term begins Monday.'));
    $firstNotification = $firstTeacher->notifications()->firstOrFail();
    $secondNotification = $secondTeacher->notifications()->firstOrFail();

    $this->actingAs($firstTeacher)
        ->get(route('teacher.notifications.show', $firstNotification->id))
        ->assertOk()
        ->assertSee('The new term begins Monday.');

    expect($firstNotification->fresh()->read_at)->not->toBeNull()
        ->and($secondNotification->fresh()->read_at)->toBeNull();

    $this->actingAs($secondTeacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('1 unread');
});

test('teachers cannot open another teachers notification', function () {
    $firstTeacher = User::factory()->create();
    $firstTeacher->assignRole('Teacher');
    $secondTeacher = User::factory()->create();
    $secondTeacher->assignRole('Teacher');
    $firstTeacher->notify(new AnnouncementNotification(5, 'Private notification', 'Only for the first teacher.'));
    $notification = $firstTeacher->notifications()->firstOrFail();

    $this->actingAs($secondTeacher)
        ->get(route('teacher.notifications.show', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

test('admins and students cannot access teacher notification pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $student = User::factory()->create();
    $student->assignRole('Student');

    $this->actingAs($admin)->get(route('teacher.notifications.index'))->assertForbidden();
    $this->actingAs($student)->get(route('teacher.notifications.index'))->assertForbidden();
});

test('admin announcement job creates teacher notifications without changing teacher email dispatch', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');

    $announcement = Announcement::create([
        'title' => 'Important notice',
        'message' => 'Staff meeting at 3 PM.',
        'audience' => 'teachers',
        'created_by' => $admin->id,
    ]);

    SendAnnouncementNotificationJob::dispatchSync($announcement);

    expect($teacher->notifications()->where('type', AnnouncementNotification::class)->count())->toBe(1)
        ->and($admin->notifications()->where('type', AnnouncementNotification::class)->count())->toBe(0);

    Queue::fake();
    app(AnnouncementService::class)->createForTeacher([
        'title' => 'Class update',
        'message' => 'Room changed.',
        'audience' => 'students',
    ], $teacher);

    Queue::assertPushed(SendTeacherAnnouncementEmailsJob::class);
});
