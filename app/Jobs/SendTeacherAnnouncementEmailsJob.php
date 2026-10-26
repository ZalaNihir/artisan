<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\Student;
use App\Models\StudentParent;
use App\Notifications\TeacherAnnouncementEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendTeacherAnnouncementEmailsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function handle(): void
    {
        if (! in_array($this->announcement->audience, ['students', 'parents', 'both'], true)
            || ! $this->announcement->author?->hasRole('Teacher')) {
            return;
        }

        $teacherId = $this->announcement->created_by;
        $notification = new TeacherAnnouncementEmailNotification(
            $this->announcement->title,
            $this->announcement->message,
        );
        $sentEmails = [];

        if (in_array($this->announcement->audience, ['students', 'both'], true)) {
            Student::query()
                ->where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->whereNotNull('email')
                ->where('email', '<>', '')
                ->select(['id', 'email'])
                ->chunkById(200, function ($students) use (&$sentEmails, $notification): void {
                    foreach ($students as $student) {
                        $this->notifyEmail($student->email, $notification, $sentEmails);
                    }
                });
        }

        if (in_array($this->announcement->audience, ['parents', 'both'], true)) {
            StudentParent::query()
                ->whereHas('students', fn (Builder $students) => $students
                    ->where('teacher_id', $teacherId)
                    ->where('status', 'active'))
                ->whereNotNull('email')
                ->where('email', '<>', '')
                ->select(['id', 'email'])
                ->chunkById(200, function ($parents) use (&$sentEmails, $notification): void {
                    foreach ($parents as $parent) {
                        $this->notifyEmail($parent->email, $notification, $sentEmails);
                    }
                });
        }
    }

    private function notifyEmail(string $email, Notification $notification, array &$sentEmails): void
    {
        $normalizedEmail = mb_strtolower(trim($email));

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) || isset($sentEmails[$normalizedEmail])) {
            return;
        }

        $sentEmails[$normalizedEmail] = true;
        NotificationFacade::route('mail', $normalizedEmail)->notify($notification);
    }
}
