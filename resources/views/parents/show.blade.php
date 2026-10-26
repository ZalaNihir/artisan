<x-layouts::app :title="__('Parent details')">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('parents.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white">{{ __('Parents') }}</a>
                <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $parent->first_name }} {{ $parent->last_name }}</h1>
            </div>
            <div class="flex gap-3">
                @can('parents.update')
                    <a href="{{ route('parents.edit', $parent) }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">{{ __('Edit') }}</a>
                @endcan
                @can('parents.delete')
                    <form method="POST" action="{{ route('parents.destroy', $parent) }}" onsubmit="return confirm('{{ __('Delete this parent record? Linked students will remain.') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2.5 text-sm font-medium text-red-700 dark:border-red-900 dark:text-red-300">{{ __('Delete') }}</button>
                    </form>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        <section class="space-y-8 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            <dl class="grid gap-5 sm:grid-cols-2">
                @foreach (['phone' => __('Phone'), 'alternate_phone' => __('Alternate phone'), 'email' => __('Email'), 'occupation' => __('Occupation'), 'address' => __('Address')] as $field => $label)
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line text-sm text-zinc-900 dark:text-white">{{ $parent->{$field} ?: '—' }}</dd></div>
                @endforeach
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Created by') }}</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $parent->creator?->name ?? '—' }}</dd></div>
            </dl>

            <div class="space-y-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Associated children') }}</h2>
                <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    @forelse ($parent->students as $student)
                        <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('students.show', $student) }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">{{ $student->first_name }} {{ $student->last_name }}</a>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $student->pivot->relationship ?: __('Relationship not specified') }}@if ($student->pivot->is_primary) · {{ __('Primary contact') }}@endif</span>
                        </div>
                    @empty
                        <p class="px-4 py-5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No children are associated with this parent.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
