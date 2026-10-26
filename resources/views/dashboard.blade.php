<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @role('Teacher')
            @unlessrole('Admin')
                @can('announcements.view')
                    <a href="{{ route('teacher.notifications.index') }}" class="flex items-center justify-between gap-4 rounded-xl border border-blue-200 bg-blue-50 p-5 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/30 dark:hover:bg-blue-950/50">
                        <div>
                            <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Teacher Notifications') }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Read announcements published by the administration.') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-700 px-3 py-1 text-sm font-semibold text-white">{{ __(':count unread', ['count' => $unreadAnnouncementCount]) }}</span>
                    </a>
                @endcan
            @endunlessrole
        @endrole
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
