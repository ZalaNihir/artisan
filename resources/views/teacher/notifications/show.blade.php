<x-layouts::app :title="$notification->data['title'] ?? __('Announcement')">
    <article class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-8">
        <a href="{{ route('teacher.notifications.index') }}" class="text-sm font-medium text-blue-700 hover:underline dark:text-blue-300">&larr; {{ __('All announcements') }}</a>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $notification->data['title'] ?? __('Announcement') }}</h1>
                <time class="shrink-0 text-sm text-zinc-500 dark:text-zinc-400" datetime="{{ $notification->created_at->toAtomString() }}">{{ $notification->created_at->format('M j, Y · g:i A') }}</time>
            </div>
            <div class="mt-6 whitespace-pre-wrap leading-7 text-zinc-700 dark:text-zinc-200">{{ $notification->data['message'] ?? '' }}</div>
        </div>
    </article>
</x-layouts::app>
