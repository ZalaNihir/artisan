<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function __construct(private TeacherNotificationService $notifications) {}

    public function summaryFor(User $user): array
    {
        $counts = [
            'teachers' => 0,
            'students' => 0,
            'parents' => 0,
            'unread_notifications' => 0,
        ];

        if ($user->hasRole('Admin')) {
            $counts['teachers'] = User::query()
                ->role('Teacher')
                ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'Admin'))
                ->count();
            $counts['students'] = Student::query()->count();
            $counts['parents'] = StudentParent::query()->count();

            return ['role' => 'admin', 'counts' => $counts];
        }

        if ($user->hasRole('Teacher')) {
            $counts['students'] = $user->students()->count();
            $counts['parents'] = StudentParent::query()
                ->whereHas('students', fn (Builder $query) => $query->where('students.teacher_id', $user->getKey()))
                ->distinct()
                ->count('parents.id');
            $counts['unread_notifications'] = $this->notifications->unreadAnnouncementCount($user);

            return ['role' => 'teacher', 'counts' => $counts];
        }

        return ['role' => null, 'counts' => $counts];
    }
}
