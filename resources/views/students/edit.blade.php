<x-layouts::app :title="__('Edit student')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div><a href="{{ route('students.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white">{{ __('Students') }}</a><h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Edit student') }}</h1><p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $student->first_name }} {{ $student->last_name }}</p></div>
        <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" class="space-y-5 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @csrf @method('PUT')
            @include('students._form', ['student' => $student])
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end"><a href="{{ route('students.show', $student) }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">{{ __('Cancel') }}</a><button class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">{{ __('Save changes') }}</button></div>
        </form>
    </div>
</x-layouts::app>
