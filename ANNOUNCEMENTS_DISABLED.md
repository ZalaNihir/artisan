# Announcements temporarily disabled

The Announcements implementation is preserved in the working tree but is not reachable through the application workflow.

## Removed from the active workflow

- Removed the `admin.announcements.*` resource routes from `routes/web.php`. This disables index, create, store, edit, update, and destroy endpoints.
- Removed the Announcements item from `resources/views/layouts/app/sidebar.blade.php`.
- Removed the teacher announcements section from `resources/views/dashboard.blade.php` and restored the dashboard route to render that view directly.
- Removed the announcement author relationship from `app/Models/User.php`.
- Moved the announcement tests out of `TeacherManagementTest.php` into `tests/Feature/Admin/AnnouncementManagementTest.php` and marked them skipped while the routes are disabled.

## Preserved for restoration

- `app/Http/Controllers/Admin/AnnouncementController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Requests/Admin/StoreAnnouncementRequest.php`
- `app/Http/Requests/Admin/UpdateAnnouncementRequest.php`
- `app/Models/Announcement.php`
- `app/Services/AnnouncementService.php`
- `database/migrations/2026_10_26_170358_create_announcements_table.php`
- `resources/views/admin/announcements/index.blade.php`
- `resources/views/admin/announcements/create.blade.php`
- `resources/views/admin/announcements/edit.blade.php`
- `tests/Feature/Admin/AnnouncementManagementTest.php`

The existing announcement permission definitions in `database/seeders/RolePermissionSeeder.php` are unchanged. The migration is also unchanged and was not run during this task; Laravel will still apply it on a future `php artisan migrate` if it is pending.

## Restore later

Re-register the Admin announcement resource routes, restore the dashboard controller route and teacher dashboard section, restore the `User::createdAnnouncements()` relationship if needed, and remove the temporary skip markers from `AnnouncementManagementTest.php`.
