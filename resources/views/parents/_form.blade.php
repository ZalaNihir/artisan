@php($values = fn (string $field) => old($field, $parent?->{$field}))
@php($selectedStudents = $parent?->students?->keyBy('id') ?? collect())

<div class="grid gap-5 sm:grid-cols-2">
    @foreach (['first_name' => __('First name'), 'last_name' => __('Last name')] as $field => $label)
        <div>
            <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $label }}</label>
            <input id="{{ $field }}" name="{{ $field }}" value="{{ $values($field) }}" required maxlength="100" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error($field) border-red-500 @enderror">
            @error($field)<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Phone') }}</label>
        <input id="phone" name="phone" value="{{ $values('phone') }}" required maxlength="20" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('phone') border-red-500 @enderror">
        @error('phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="alternate_phone" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Alternate phone') }}</label>
        <input id="alternate_phone" name="alternate_phone" value="{{ $values('alternate_phone') }}" maxlength="20" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('alternate_phone') border-red-500 @enderror">
        @error('alternate_phone')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Email') }}</label>
        <input id="email" name="email" type="email" value="{{ $values('email') }}" maxlength="255" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('email') border-red-500 @enderror">
        @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="occupation" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Occupation') }}</label>
        <input id="occupation" name="occupation" value="{{ $values('occupation') }}" maxlength="150" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('occupation') border-red-500 @enderror">
        @error('occupation')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="address" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Address') }}</label>
    <textarea id="address" name="address" rows="3" class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('address') border-red-500 @enderror">{{ $values('address') }}</textarea>
    @error('address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<fieldset class="space-y-4">
    <legend class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Children') }}</legend>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Select the students associated with this parent. Your list includes only students you teach.') }}</p>
    @error('students')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    <div class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
        @forelse ($students as $student)
            @php($association = $selectedStudents->get($student->id))
            @php($associationInput = old("students.{$student->id}", $association ? ['selected' => true, 'relationship' => $association->pivot->relationship, 'is_primary' => $association->pivot->is_primary] : []))
            <div class="grid gap-4 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-center">
                <label class="flex items-center gap-3 text-sm font-medium text-zinc-900 dark:text-white">
                    <input type="hidden" name="students[{{ $student->id }}][selected]" value="0">
                    <input type="checkbox" name="students[{{ $student->id }}][selected]" value="1" @checked($associationInput['selected'] ?? false) class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    {{ $student->first_name }} {{ $student->last_name }}
                </label>
                <input name="students[{{ $student->id }}][relationship]" value="{{ $associationInput['relationship'] ?? '' }}" maxlength="50" placeholder="{{ __('Relationship (e.g. Mother)') }}" aria-label="{{ __('Relationship to :student', ['student' => $student->first_name.' '.$student->last_name]) }}" class="block w-full rounded-lg border-zinc-300 text-sm shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                    <input type="checkbox" name="students[{{ $student->id }}][is_primary]" value="1" @checked($associationInput['is_primary'] ?? false) class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                    {{ __('Primary') }}
                </label>
            </div>
        @empty
            <p class="p-4 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No students are available to associate with this parent.') }}</p>
        @endforelse
    </div>
</fieldset>
