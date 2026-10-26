<x-layouts::app :title="__('Add teacher')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Teachers') }}</a>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Create teacher account') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('The teacher can use these credentials to sign in.') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @csrf
            <div>
                <flux:input label="{{ __('Full name') }}" name="name" :value="old('name')" required autocomplete="name" />
            </div>
            <div>
                <flux:input label="{{ __('Email address') }}" name="email" type="email" :value="old('email')" required autocomplete="email" />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <flux:input label="{{ __('Password') }}" name="password" type="password" required autocomplete="new-password" />
                </div>
                <div>
                    <flux:input label="{{ __('Confirm password') }}" name="password_confirmation" type="password" required autocomplete="new-password" />
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800" wire:navigate>{{ __('Cancel') }}</a>
                <flux:button variant="primary" type="submit">{{ __('Create teacher') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
