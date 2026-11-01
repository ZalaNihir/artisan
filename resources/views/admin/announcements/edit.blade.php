<div>
    <!-- An unexamined life is not worth living. - Socrates -->
</div>
<x-layouts::app :title="__('Edit announcement')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('admin.announcements.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Announcements') }}</a>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Edit announcement') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Update the message shown to teachers.') }}</p>
        </div>

        @if (session('error'))
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @csrf
            @method('PUT')
            <div>
                <flux:input label="{{ __('Title') }}" name="title" :value="old('title', $announcement->title)" required maxlength="255" />
            </div>
            <div>
                <flux:textarea label="{{ __('Message') }}" name="message" rows="8" required>{{ old('message', $announcement->message) }}</flux:textarea>
            </div>
            <div>
                <label for="scheduled_at" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Schedule for (optional)') }}</label>
                <input id="scheduled_at" type="datetime-local" name="scheduled_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('scheduled_at', $announcement->scheduled_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                @error('scheduled_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Leave blank to publish immediately.') }}</p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800" wire:navigate>{{ __('Cancel') }}</a>
                <flux:button variant="primary" type="submit">{{ __('Save changes') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
