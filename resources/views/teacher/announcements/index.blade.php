<x-layouts::app :title="__('My announcements')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('My announcements') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Announcements you have shared with students and parents.') }}</p>
            </div>
            @can('announcements.create')
                <a href="{{ route('teacher.announcements.create') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900">{{ __('New announcement') }}</a>
            @endcan
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <div class="space-y-3">
            @forelse ($announcements as $announcement)
                <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $announcement->title }}</h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Audience: :audience', ['audience' => match ($announcement->audience) {'students' => __('Students'), 'parents' => __('Parents'), 'both' => __('Students and parents'), default => __('Teachers')}]) }} · {{ $announcement->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $announcement->message }}</p>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-10 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">{{ __('You have not created any announcements yet.') }}</div>
            @endforelse
        </div>
        @if ($announcements->hasPages())
            <div>{{ $announcements->links() }}</div>
        @endif
    </div>
</x-layouts::app>
