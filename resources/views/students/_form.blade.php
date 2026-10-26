@php($values = fn(string $field, mixed $default = null) => old($field, $student?->{$field} ?? $default))
@php($dateOfBirth = old('dob', $student?->dob?->toDateString()))
<div class="grid gap-5 sm:grid-cols-2">
    @foreach (['first_name' => __('First name'), 'last_name' => __('Last name')] as $field => $label)
        <div><label for="{{ $field }}"
                class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $label }}</label><input
                id="{{ $field }}" name="{{ $field }}" value="{{ $values($field) }}" required maxlength="255"
                class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error($field) border-red-500 @enderror">
            @error($field)
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
    <div><label for="email"
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Email') }}</label><input
            id="email" name="email" type="email" value="{{ $values('email') }}"
            class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('email') border-red-500 @enderror">
        @error('email')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div><label for="phone_number"
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Phone number') }}</label><input
            id="phone_number" name="phone_number" value="{{ $values('phone_number') }}"
            class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('phone_number') border-red-500 @enderror">
        @error('phone_number')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div><label for="dob"
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Date of birth') }}</label><input
            id="dob" name="dob" type="date" value="{{ $dateOfBirth }}"
            class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('dob') border-red-500 @enderror">
        @error('dob')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
            {{ __('Gender') }}
        </label>

        <div class="flex items-center gap-6 mt-2">
            <!-- Male Option -->
            <label class="flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-200 cursor-pointer">
                <input type="radio" name="gender" value="male"
                    {{ old('gender', $values('gender')) == 'male' ? 'checked' : '' }}
                    class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 @error('gender') border-red-500 @enderror">
                <span class="ms-2">{{ __('Male') }}</span>
            </label>

            <!-- Female Option -->
            <label class="flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-200 cursor-pointer">
                <input type="radio" name="gender" value="female"
                    {{ old('gender', $values('gender')) == 'female' ? 'checked' : '' }}
                    class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 @error('gender') border-red-500 @enderror">
                <span class="ms-2">{{ __('Female') }}</span>
            </label>
        </div>

        @error('gender')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div><label for="status"
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Status') }}</label><select
            id="status" name="status" required
            class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            @foreach (['active' => __('Active'), 'inactive' => __('Inactive')] as $value => $label)
                <option value="{{ $value }}" @selected($values('status', 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
@if (auth()->user()->hasRole('Admin'))
    <div><label for="teacher_id"
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Teacher') }}</label><select
            id="teacher_id" name="teacher_id" required
            class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            <option value="">{{ __('Select a teacher') }}</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $student?->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </select>
        @error('teacher_id')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
@endif
<div><label for="address"
        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Address') }}</label>
    <textarea id="address" name="address" rows="3"
        class="block w-full rounded-lg border-zinc-300 shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white @error('address') border-red-500 @enderror">{{ $values('address') }}</textarea>
    @error('address')
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
<div><label for="image"
        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Profile image') }}</label>
    @if ($student?->image)
        <div class="mb-3 flex items-center gap-3"><img src="{{ Storage::disk('public')->url($student->image) }}"
                alt="{{ $student->first_name }}" class="size-16 rounded-full object-cover"><label
                class="text-sm text-zinc-600 dark:text-zinc-300"><input type="checkbox" name="remove_image"
                    value="1" @checked(old('remove_image'))> {{ __('Remove current image') }}</label></div>
    @endif
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
        class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:font-medium dark:text-zinc-300 dark:file:bg-zinc-800">
    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('JPEG, PNG or WebP, up to 2 MB.') }}</p>
    @error('image')
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
