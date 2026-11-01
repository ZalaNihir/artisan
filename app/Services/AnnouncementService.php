<?php

namespace App\Services;

use App\Jobs\SendAnnouncementNotificationJob;
use App\Jobs\SendTeacherAnnouncementEmailsJob;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
        $scheduledAt = $attributes['scheduled_at'] ?? null;
        $announcement = DB::transaction(fn (): Announcement => Announcement::create([
            ...Arr::only($attributes, ['title', 'message']),
            'audience' => 'teachers',
            'created_by' => $author->getKey(),
            'scheduled_at' => $scheduledAt ? Carbon::parse($scheduledAt) : null,
            'sent_at' => $scheduledAt ? null : now(),
        ]));

        if (! $scheduledAt) SendAnnouncementNotificationJob::dispatch($announcement);

        return $announcement;
    }

    public function paginateForTeacher(User $teacher): LengthAwarePaginator
    {
        $this->authorizeTeacherAction($teacher, 'announcements.view');

        return Announcement::query()
            ->whereBelongsTo($teacher, 'author')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function createForTeacher(array $attributes, User $teacher): Announcement
    {
        $this->authorizeTeacherAction($teacher, 'announcements.create');
        $scheduledAt = $attributes['scheduled_at'] ?? null;

        $announcement = DB::transaction(fn (): Announcement => Announcement::create([
            ...Arr::only($attributes, ['title', 'message', 'audience']),
            'created_by' => $teacher->getKey(),
            'scheduled_at' => $scheduledAt ? Carbon::parse($scheduledAt) : null,
            'sent_at' => $scheduledAt ? null : now(),
        ]));

        if (! $scheduledAt) SendTeacherAnnouncementEmailsJob::dispatch($announcement);

        return $announcement;
    }

    public function update(Announcement $announcement, array $attributes): Announcement
    {
        $updated = DB::transaction(function () use ($announcement, $attributes): Announcement {
            $announcement = Announcement::query()->lockForUpdate()->findOrFail($announcement->id);
            if ($announcement->isSent()) {
                throw new AuthorizationException(__('Sent announcements cannot be edited.'));
            }
            $scheduledAt = $attributes['scheduled_at'] ?? null;
            $announcement->fill(Arr::only($attributes, ['title', 'message']))
                ->forceFill(['scheduled_at' => $scheduledAt ? Carbon::parse($scheduledAt) : null, 'sent_at' => $scheduledAt ? null : now()])
                ->save();

            return $announcement;
        });
        if (! $updated->scheduled_at) {
            if ($updated->audience === 'teachers') SendAnnouncementNotificationJob::dispatch($updated);
            else SendTeacherAnnouncementEmailsJob::dispatch($updated);
        }

        return $updated;
    }

    public function publishDue(): int
    {
        $published = 0;
        Announcement::query()->whereNull('sent_at')->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())->orderBy('id')->chunkById(100, function ($announcements) use (&$published): void {
                foreach ($announcements as $announcement) {
                    $claimed = Announcement::query()->whereKey($announcement->id)->whereNull('sent_at')
                        ->where('scheduled_at', '<=', now())->update(['sent_at' => now(), 'updated_at' => now()]);
                    if (! $claimed) continue;
                    $announcement->refresh();
                    if ($announcement->audience === 'teachers') SendAnnouncementNotificationJob::dispatch($announcement);
                    else SendTeacherAnnouncementEmailsJob::dispatch($announcement);
                    $published++;
                }
            });
        return $published;
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
            ->where('audience', 'teachers')
            ->whereNotNull('sent_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function authorizeTeacherAction(User $teacher, string $permission): void
    {
        if (! $teacher->hasRole('Teacher') || $teacher->hasRole('Admin') || ! $teacher->can($permission)) {
            throw new AuthorizationException;
        }
    }
}
