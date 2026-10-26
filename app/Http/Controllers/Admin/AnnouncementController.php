<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(protected AnnouncementService $announcementsService) {}

    public function index(): View
    {
        return view('admin.announcements.index', ['announcements' => $this->announcementsService->paginate()]);
    }

    public function create(): View
    {
        return view('admin.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->announcementsService->create($request->validated(), $request->user());

        return to_route('admin.announcements.index')->with('success', __('Announcement published.'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', ['announcement' => $announcement]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->announcementsService->update($announcement, $request->validated());

        return to_route('admin.announcements.index')->with('success', __('Announcement updated.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->announcementsService->delete($announcement);

        return to_route('admin.announcements.index')->with('success', __('Announcement deleted.'));
    }
}
