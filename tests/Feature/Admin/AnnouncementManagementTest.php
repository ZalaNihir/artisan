<?php

use App\Models\Announcement;
use App\Models\User;
use App\Services\AnnouncementService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;

function createAnnouncementModuleAdmin(): User
{
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

test('Admin can create, edit, and delete announcements without sending email', function () {
    $admin = createAnnouncementModuleAdmin();
    Mail::fake();

    $this->actingAs($admin)->get(route('admin.announcements.index'))->assertOk();
    $this->get(route('admin.announcements.create'))->assertOk();

    $this->post(route('admin.announcements.store'), [
        'title' => 'Staff meeting',
        'message' => 'Please attend the staff meeting at 3 PM.',
    ])->assertRedirect(route('admin.announcements.index'));

    $announcement = Announcement::query()->where('title', 'Staff meeting')->firstOrFail();
    expect($announcement->created_by)->toBe($admin->id);

    $this->get(route('admin.announcements.edit', $announcement))
        ->assertOk()
        ->assertSee('Staff meeting');

    $this->put(route('admin.announcements.update', $announcement), [
        'title' => 'Updated staff meeting',
        'message' => 'The meeting starts at 3:30 PM.',
    ])->assertRedirect(route('admin.announcements.index'));

    $this->assertDatabaseHas('announcements', [
        'id' => $announcement->id,
        'title' => 'Updated staff meeting',
        'message' => 'The meeting starts at 3:30 PM.',
        'created_by' => $admin->id,
    ]);

    $this->delete(route('admin.announcements.destroy', $announcement))
        ->assertRedirect(route('admin.announcements.index'));

    $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    Mail::assertNothingOutgoing();
})->skip('Announcements are temporarily disabled.');

test('teachers see the latest announcements on their dashboard while other users do not', function () {
    $admin = createAnnouncementModuleAdmin();
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');
    $announcements = app(AnnouncementService::class);
    $announcements->create(['title' => 'Earlier notice', 'message' => 'Earlier message.'], $admin);
    $announcements->create(['title' => 'Latest notice', 'message' => 'Latest message.'], $admin);

    $this->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Latest notice', 'Earlier notice']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Latest notice');
})->skip('Announcements are temporarily disabled.');

test('teachers cannot access Admin announcement management routes', function () {
    createAnnouncementModuleAdmin();
    $teacher = User::factory()->create();
    $teacher->assignRole('Teacher');

    $this->actingAs($teacher)
        ->get(route('admin.announcements.index'))
        ->assertForbidden();
})->skip('Announcements are temporarily disabled.');
