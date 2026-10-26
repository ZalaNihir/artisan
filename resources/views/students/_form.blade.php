@php($values = fn(string $field, mixed $default = null) => old($field, $student?->{$field} ?? $default))
@php($dateOfBirth = old('dob', $student?->dob?->toDateString()))
<div class="grid gap-5 sm:grid-cols-2">
    @foreach (['first_name' => __('First name'), 'last_name' => __('Last name')] as $field => $label)
        <flux:input label="{{ $label }}" name="{{ $field }}" :value="$values($field)" required maxlength="255" />
    @endforeach
    <flux:input label="{{ __('Email') }}" name="email" type="email" :value="$values('email')" />
    <flux:input label="{{ __('Phone number') }}" name="phone_number" :value="$values('phone_number')" />
    <flux:input label="{{ __('Date of birth') }}" name="dob" type="date" :value="$dateOfBirth" />
    <div>
        <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
            {{ __('Gender') }}
        </label>

        <div class="mt-2 flex items-center gap-6">
            <!-- Male Option -->
            <label class="flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-200 cursor-pointer">
                <input type="radio" name="gender" value="male"
                    {{ old('gender', $values('gender')) == 'male' ? 'checked' : '' }}
                    class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900 @error('gender') border-red-500 @enderror">
                <span class="ms-2">{{ __('Male') }}</span>
            </label>

            <!-- Female Option -->
            <label class="flex items-center text-sm font-medium text-zinc-700 dark:text-zinc-200 cursor-pointer">
                <input type="radio" name="gender" value="female"
                    {{ old('gender', $values('gender')) == 'female' ? 'checked' : '' }}
                    class="h-4 w-4 border-zinc-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900 @error('gender') border-red-500 @enderror">
                <span class="ms-2">{{ __('Female') }}</span>
            </label>
        </div>

        @error('gender')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <flux:select label="{{ __('Status') }}" name="status" required>
            @foreach (['active' => __('Active'), 'inactive' => __('Inactive')] as $value => $label)
                <option value="{{ $value }}" @selected($values('status', 'active') === $value)>{{ $label }}</option>
            @endforeach
    </flux:select>
</div>
@if (auth()->user()->hasRole('Admin'))
    <flux:select label="{{ __('Teacher') }}" name="teacher_id" required>
            <option value="">{{ __('Select a teacher') }}</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) old('teacher_id', $student?->teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
    </flux:select>
@endif
<flux:textarea label="{{ __('Address') }}" name="address" rows="3">{{ $values('address') }}</flux:textarea>
<div><label for="image"
        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Profile image') }}</label>
    @if ($student?->image)
        <div class="mb-3 flex items-center gap-3"><img src="{{ Storage::disk('public')->url($student->image) }}"
                alt="{{ $student->first_name }}" class="size-16 rounded-full object-cover"><label
                class="text-sm text-zinc-600 dark:text-zinc-300"><input type="checkbox" name="remove_image"
                    value="1" @checked(old('remove_image')) class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 dark:border-zinc-600 dark:bg-zinc-800 dark:focus:ring-offset-zinc-900"> {{ __('Remove current image') }}</label></div>
    @endif
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
        class="block w-full rounded-lg border border-zinc-200 bg-white text-sm text-zinc-600 shadow-xs file:mr-4 file:rounded-s-lg file:border-0 file:border-e file:border-zinc-200 file:bg-zinc-50 file:px-4 file:py-2.5 file:font-medium file:text-zinc-700 hover:file:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:file:border-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-200 dark:hover:file:bg-zinc-700">
    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('JPEG, PNG or WebP, up to 2 MB.') }}</p>
    @error('image')
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
