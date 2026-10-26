<x-layouts::app :title="__('Teacher Notifications')">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Announcements for Teachers') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Updates published by the administration.') }}</flux:text>
            </div>
            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ __(':count unread', ['count' => $unreadAnnouncementCount]) }}
            </div>
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:heading>{{ __('No announcements yet') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Admin announcements for teachers will appear here.') }}</flux:text>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($notifications as $notification)
                        <li class="{{ $notification->read_at ? '' : 'bg-blue-50/70 dark:bg-blue-950/20' }}">
                            <a href="{{ route('teacher.notifications.show', $notification->id) }}" class="block p-5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70 sm:px-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="font-semibold text-zinc-900 dark:text-white">{{ $notification->data['title'] ?? __('Announcement') }}</h2>
                                            @if ($notification->read_at)
                                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ __('Read') }}</span>
                                            @else
                                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ __('Unread') }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $notification->data['message'] ?? '' }}</p>
                                    </div>
                                    <time class="shrink-0 text-sm text-zinc-500 dark:text-zinc-400" datetime="{{ $notification->created_at->toAtomString() }}">{{ $notification->created_at->format('M j, Y') }}</time>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            {{ $notifications->links() }}
        @endif
    </div>
</x-layouts::app>
