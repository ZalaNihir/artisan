<x-layouts::app :title="__('New announcement')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('teacher.announcements.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white">{{ __('My announcements') }}</a>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('New announcement') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('This announcement will be emailed to the selected audience for your students.') }}</p>
        </div>

        <form method="POST" action="{{ route('teacher.announcements.store') }}" class="space-y-5 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @csrf
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Title') }}</label>
                <input id="title" name="title" value="{{ old('title') }}" required maxlength="255" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('title') border-red-500 @enderror">
                @error('title')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="audience" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Audience') }}</label>
                <select id="audience" name="audience" required class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('audience') border-red-500 @enderror">
                    <option value="">{{ __('Select an audience') }}</option>
                    @foreach (['students' => __('Students'), 'parents' => __('Parents'), 'both' => __('Students and parents')] as $value => $label)
                        <option value="{{ $value }}" @selected(old('audience') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('audience')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Message') }}</label>
                <textarea id="message" name="message" rows="8" required maxlength="10000" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end">
                <a href="{{ route('teacher.announcements.index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">{{ __('Cancel') }}</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">{{ __('Create announcement') }}</button>
            </div>
        </form>
    </div>
</x-layouts::app>
