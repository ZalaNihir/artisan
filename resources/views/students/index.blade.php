<x-layouts::app :title="__('Students')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Students') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage student records.') }}</p>
            </div>
            @can('students.create')
                <a href="{{ route('students.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900">{{ __('Add student') }}</a>
            @endcan
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}</div>
        @endif

        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-left dark:divide-zinc-700">
                    <thead
                        class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-3">{{ __('Student') }}</th>
                            <th class="px-5 py-3">{{ __('Email') }}</th>
                            <th class="px-5 py-3">{{ __('Teacher') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-sm dark:divide-zinc-800">
                        @forelse ($students as $student)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-zinc-900 dark:text-white"><a
                                        class="hover:underline"
                                        href="{{ route('students.show', $student) }}">{{ $student->first_name }}
                                        {{ $student->last_name }}</a></td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $student->email ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $student->teacher->name }}</td>
                                <td class="px-5 py-4"><span
                                        class="rounded-full px-2.5 py-1 text-xs font-medium {{ $student->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ __(ucfirst($student->status)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('students.show', $student) }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('View') }}</a>
                                        @can('students.update')
                                            <a href="{{ route('students.edit', $student) }}"
                                                class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('students.delete')
                                            <form method="POST" action="{{ route('students.destroy', $student) }}"
                                                onsubmit="return confirm('{{ __('Delete this student?') }}')">@csrf
                                                @method('DELETE')<button
                                                    class="font-medium text-red-600 hover:text-red-800 dark:text-red-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                    {{ __('No students have been added yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($students->hasPages())
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">{{ $students->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
