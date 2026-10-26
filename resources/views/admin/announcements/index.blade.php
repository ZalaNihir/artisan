<div>
    <!-- The only way to do great work is to love what you do. - Steve Jobs -->
</div>
<x-layouts::app :title="__('Announcements')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Dashboard') }}</a>
                <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Announcements') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Share updates with teachers on their dashboard.') }}</p>
            </div>
            @can('announcements.create')
                <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200" wire:navigate>{{ __('New announcement') }}</a>
            @endcan
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="space-y-3">
            @forelse ($announcements as $announcement)
                <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $announcement->title }}</h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Posted by :name on :date', ['name' => $announcement->author->name, 'date' => $announcement->created_at->format('M j, Y')]) }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            @can('announcements.update')
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" wire:navigate>{{ __('Edit') }}</a>
                            @endcan
                            @can('announcements.delete')
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('{{ __('Delete this announcement?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400">{{ __('Delete') }}</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $announcement->message }}</p>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-10 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">{{ __('No announcements have been posted.') }}</div>
            @endforelse
        </div>
        @if ($announcements->hasPages())
            <div>{{ $announcements->links() }}</div>
        @endif
    </div>
</x-layouts::app>
