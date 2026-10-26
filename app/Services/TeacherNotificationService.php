<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

class TeacherNotificationService
{
    public function paginateAnnouncements(User $teacher): LengthAwarePaginator
    {
        $this->authorizeTeacher($teacher);

        return $teacher->notifications()
            ->where('type', AnnouncementNotification::class)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function unreadAnnouncementCount(User $teacher): int
    {
        if (! $teacher->hasRole('Teacher') || $teacher->hasRole('Admin')) {
            return 0;
        }

        return $teacher->unreadNotifications()
            ->where('type', AnnouncementNotification::class)
            ->count();
    }

    public function findAndMarkAnnouncementAsRead(User $teacher, string $notificationId): DatabaseNotification
    {
        $this->authorizeTeacher($teacher);

        $notification = $teacher->notifications()
            ->where('type', AnnouncementNotification::class)
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $notification;
    }

    private function authorizeTeacher(User $teacher): void
    {
        if (! $teacher->hasRole('Teacher') || $teacher->hasRole('Admin') || ! $teacher->can('announcements.view')) {
            throw new AuthorizationException;
        }
    }
}
