<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $announcementId,
        public string $title,
        public string $message,
    ) {}

    /**
     * Get the notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcementId,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
