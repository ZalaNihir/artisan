<?php

namespace App\Services;

use App\Jobs\SendAnnouncementNotificationJob;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    public function paginate(): LengthAwarePaginator
    {
        return Announcement::query()
            ->with('author:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $attributes, Authenticatable $author): Announcement
    {
        $announcement = DB::transaction(fn (): Announcement => Announcement::create([
            ...Arr::only($attributes, ['title', 'message']),
            'created_by' => $author->getKey(),
        ]));

        SendAnnouncementNotificationJob::dispatch($announcement);

        return $announcement;
    }

    public function update(Announcement $announcement, array $attributes): Announcement
    {
        return DB::transaction(function () use ($announcement, $attributes): Announcement {
            $announcement->fill(Arr::only($attributes, ['title', 'message']))->save();

            return $announcement;
        });
    }

    public function delete(Announcement $announcement): void
    {
        DB::transaction(fn () => $announcement->delete());
    }

    public function forTeacherDashboard(User $user): Collection
    {
        if (! $user->hasRole('Teacher') || $user->hasRole('Admin')) {
            return new Collection;
        }

        return Announcement::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }
}
