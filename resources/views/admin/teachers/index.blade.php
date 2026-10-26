<x-layouts::app :title="__('Teachers')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Dashboard') }}</a>
                <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Teachers') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage teacher accounts and access.') }}</p>
            </div>
            @can('teachers.create')
                <a href="{{ route('admin.teachers.create') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200" wire:navigate>{{ __('Add teacher') }}</a>
            @endcan
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-left dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-5 py-3">{{ __('Teacher') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('Email address') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('Added') }}</th>
                            <th scope="col" class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($teachers as $teacher)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-zinc-900 dark:text-white">{{ $teacher->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $teacher->email }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500 dark:text-zinc-400">{{ $teacher->created_at->format('M j, Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        @can('teachers.update')
                                            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" wire:navigate>{{ __('Edit') }}</a>
                                        @endcan
                                        @can('teachers.delete')
                                            <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('{{ __('Delete this teacher account?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-800 dark:text-red-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-zinc-500 dark:text-zinc-400">{{ __('No teacher accounts have been added yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($teachers->hasPages())
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">{{ $teachers->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
