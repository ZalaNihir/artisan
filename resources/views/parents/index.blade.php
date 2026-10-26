<x-layouts::app :title="__('Parents')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Parents') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage parent and guardian records.') }}</p>
            </div>
            @can('parents.create')
                <a href="{{ route('parents.create') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900">{{ __('Add parent') }}</a>
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
                            <th scope="col" class="px-5 py-3">{{ __('Parent') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('Phone') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('Email') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('Children') }}</th>
                            <th scope="col" class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($parents as $parent)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-zinc-900 dark:text-white"><a class="hover:underline" href="{{ route('parents.show', $parent) }}">{{ $parent->first_name }} {{ $parent->last_name }}</a></td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $parent->phone }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $parent->email ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ trans_choice(':count child|:count children', $parent->students_count, ['count' => $parent->students_count]) }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('parents.show', $parent) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('View') }}</a>
                                        @can('parents.update')
                                            <a href="{{ route('parents.edit', $parent) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('parents.delete')
                                            <form method="POST" action="{{ route('parents.destroy', $parent) }}" onsubmit="return confirm('{{ __('Delete this parent record? Linked students will remain.') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-800 dark:text-red-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-zinc-500 dark:text-zinc-400">{{ __('No parents have been added yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($parents->hasPages())
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">{{ $parents->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
