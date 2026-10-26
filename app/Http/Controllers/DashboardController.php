<?php

namespace App\Http\Controllers;

use App\Services\TeacherNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request, TeacherNotificationService $notifications): View
    {
        return view('dashboard', [
            'unreadAnnouncementCount' => $notifications->unreadAnnouncementCount($request->user()),
        ]);
    }
}
