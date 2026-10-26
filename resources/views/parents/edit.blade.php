<x-layouts::app :title="__('Edit parent')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('parents.index') }}" class="text-sm text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white">{{ __('Parents') }}</a>
            <h1 class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Edit parent') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $parent->first_name }} {{ $parent->last_name }}</p>
        </div>
        <form method="POST" action="{{ route('parents.update', $parent) }}" class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @csrf
            @method('PUT')
            @include('parents._form', ['parent' => $parent])
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end">
                <a href="{{ route('parents.show', $parent) }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:text-zinc-200">{{ __('Cancel') }}</a>
                <flux:button variant="primary" type="submit">{{ __('Save changes') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
