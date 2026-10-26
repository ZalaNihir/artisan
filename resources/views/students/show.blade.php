<x-layouts::app :title="__('Student details')">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><a href="{{ route('students.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white">{{ __('Students') }}</a><h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $student->first_name }} {{ $student->last_name }}</h1></div><div class="flex gap-3">@can('students.update')<a href="{{ route('students.edit', $student) }}" class="rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">{{ __('Edit') }}</a>@endcan</div></div>
        @if (session('success'))<div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <section class="grid gap-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-[auto_1fr] sm:p-8">
            @if ($student->image)<img src="{{ Storage::disk('public')->url($student->image) }}" alt="{{ $student->first_name }} {{ $student->last_name }}" class="size-28 rounded-full object-cover">@endif
            <dl class="grid gap-5 sm:grid-cols-2">
                @foreach (['grade' => __('Grade'), 'email' => __('Email'), 'phone_number' => __('Phone number'), 'dob' => __('Date of birth'), 'gender' => __('Gender'), 'address' => __('Address'), 'status' => __('Status')] as $field => $label)
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $label }}</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $field === 'dob' ? $student->dob?->format('M j, Y') : ($student->{$field} ?: '—') }}</dd></div>
                @endforeach
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Teacher') }}</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $student->teacher->name }}</dd></div>
            </dl>
        </section>
    </div>
</x-layouts::app>
