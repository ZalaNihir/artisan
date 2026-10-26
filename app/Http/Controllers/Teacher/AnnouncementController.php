<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Requests\Teacher\StoreAnnouncementRequest;
use App\Services\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\View\View;

class AnnouncementController extends RoutingController
{
    public function __construct(protected AnnouncementService $announcements)
    {
        $this->middleware('can:announcements.view')->only('index');
        $this->middleware('can:announcements.create')->only(['create', 'store']);
    }

    public function index(Request $request): View
    {
        return view('teacher.announcements.index', [
            'announcements' => $this->announcements->paginateForTeacher($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('teacher.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->announcements->createForTeacher(
            $request->validated(),
            $request->user(),
        );

        return to_route('teacher.announcements.index')
            ->with('success', __('Announcement created and queued for email delivery.'));
    }
}
