<?php

namespace App\Http\Controllers\Teacher;

use App\Services\TeacherNotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\View\View;

class NotificationController extends RoutingController
{
    public function __construct(protected TeacherNotificationService $notifications)
    {
        $this->middleware('can:announcements.view');
    }

    public function index(Request $request): View
    {
        return view('teacher.notifications.index', [
            'notifications' => $this->notifications->paginateAnnouncements($request->user()),
            'unreadAnnouncementCount' => $this->notifications->unreadAnnouncementCount($request->user()),
        ]);
    }

    public function show(Request $request, string $notification): View
    {
        return view('teacher.notifications.show', [
            'notification' => $this->notifications->findAndMarkAnnouncementAsRead($request->user(), $notification),
        ]);
    }
}
