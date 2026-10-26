<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendAnnouncementNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Announcement $announcement
    ) {}

    public function handle(): void
    {
        User::role('Teacher')
            ->select(['id', 'email'])
            ->chunk(100, function ($teachers) {
                Notification::send($teachers, new AnnouncementNotification(
                    announcementId: $this->announcement->id,
                    title: $this->announcement->title,
                    message: $this->announcement->message,
                ));
            });
    }
}
